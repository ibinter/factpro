<?php

use App\Models\Document;
use App\Models\DocumentArchive;
use App\Models\DocumentAuditLog;
use App\Services\ArchiveService;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

// ─── Setup clés RSA temporaires ─────────────────────────────────────────────

function ensureArchiveKeys(): void
{
    $keysDir = storage_path('app/keys');
    if (! is_dir($keysDir)) {
        mkdir($keysDir, 0755, true);
    }
    if (! file_exists($keysDir . '/archive_private.pem')) {
        $config = ['digest_alg' => 'sha256', 'private_key_bits' => 2048, 'private_key_type' => OPENSSL_KEYTYPE_RSA];
        $res    = openssl_pkey_new($config);
        openssl_pkey_export($res, $privateKey);
        $pubKey = openssl_pkey_get_details($res)['key'];
        file_put_contents($keysDir . '/archive_private.pem', $privateKey);
        file_put_contents($keysDir . '/archive_public.pem', $pubKey);
    }
}

// ─── Helper document finalisé ────────────────────────────────────────────────

function createFinalizedDocument(\App\Models\Company $company): Document
{
    return Document::create([
        'company_id'      => $company->id,
        'type'            => 'invoice',
        'number'          => 'FAC-' . strtoupper(Str::random(6)),
        'status'          => 'finalized',
        'issue_date'      => now()->toDateString(),
        'currency'        => 'XOF',
        'subtotal'        => 100000,
        'discount_amount' => 0,
        'tax_amount'      => 18000,
        'total'           => 118000,
        'amount_paid'     => 0,
        'finalized_at'    => now(),
    ]);
}

// ─── Tests ───────────────────────────────────────────────────────────────────

it('archives a finalized document with rsa signature', function () {
    ensureArchiveKeys();
    Storage::fake('local');

    $user    = createUserWithCompanyAndTrial();
    $company = $user->currentCompany;
    $doc     = createFinalizedDocument($company);

    $service = app(ArchiveService::class);
    $archive = $service->archive($doc);

    expect($archive)->toBeInstanceOf(DocumentArchive::class)
        ->and($archive->document_id)->toBe($doc->id)
        ->and($archive->company_id)->toBe($company->id)
        ->and($archive->signature)->not->toBeEmpty()
        ->and($archive->document_hash)->toHaveLength(64)
        ->and($archive->public_key_fingerprint)->toHaveLength(64);
});

it('hash matches pdf content', function () {
    ensureArchiveKeys();
    Storage::fake('local');

    $user    = createUserWithCompanyAndTrial();
    $company = $user->currentCompany;
    $doc     = createFinalizedDocument($company);

    $service = app(ArchiveService::class);
    $archive = $service->archive($doc);

    $pdfContent  = Storage::get($archive->pdf_path);
    $currentHash = hash('sha256', $pdfContent);

    expect($currentHash)->toBe($archive->document_hash);
});

it('signature is valid with public key', function () {
    ensureArchiveKeys();
    Storage::fake('local');

    $user    = createUserWithCompanyAndTrial();
    $company = $user->currentCompany;
    $doc     = createFinalizedDocument($company);

    $service = app(ArchiveService::class);
    $archive = $service->archive($doc);

    $publicKey = file_get_contents(storage_path('app/keys/archive_public.pem'));
    $valid     = openssl_verify(
        $archive->document_hash,
        base64_decode($archive->signature),
        $publicKey,
        OPENSSL_ALGO_SHA256
    );

    expect($valid)->toBe(1);
});

it('verifies archive integrity successfully', function () {
    ensureArchiveKeys();
    Storage::fake('local');

    $user    = createUserWithCompanyAndTrial();
    $company = $user->currentCompany;
    $doc     = createFinalizedDocument($company);

    $service = app(ArchiveService::class);
    $archive = $service->archive($doc);
    $result  = $service->verify($archive);

    expect($result['valid'])->toBeTrue()
        ->and($result['hash_match'])->toBeTrue()
        ->and($result['signature_valid'])->toBeTrue();
});

it('detects tampered archive', function () {
    ensureArchiveKeys();
    Storage::fake('local');

    $user    = createUserWithCompanyAndTrial();
    $company = $user->currentCompany;
    $doc     = createFinalizedDocument($company);

    $service = app(ArchiveService::class);
    $archive = $service->archive($doc);

    // Altère le fichier PDF dans le storage
    Storage::put($archive->pdf_path, 'CORRUPTED CONTENT');

    $result = $service->verify($archive);

    expect($result['valid'])->toBeFalse()
        ->and($result['hash_match'])->toBeFalse();
});

it('cannot archive non-finalized document', function () {
    ensureArchiveKeys();

    $user    = createUserWithCompanyAndTrial();
    $company = $user->currentCompany;

    $doc = Document::create([
        'company_id'      => $company->id,
        'type'            => 'invoice',
        'number'          => 'FAC-DRAFT-001',
        'status'          => 'draft',
        'issue_date'      => now()->toDateString(),
        'currency'        => 'XOF',
        'subtotal'        => 100000,
        'discount_amount' => 0,
        'tax_amount'      => 0,
        'total'           => 100000,
        'amount_paid'     => 0,
    ]);

    $service = app(ArchiveService::class);

    expect(fn () => $service->archive($doc))->toThrow(\Exception::class);
});

it('cannot archive same document twice', function () {
    ensureArchiveKeys();
    Storage::fake('local');

    $user    = createUserWithCompanyAndTrial();
    $company = $user->currentCompany;
    $doc     = createFinalizedDocument($company);

    $service  = app(ArchiveService::class);
    $archive1 = $service->archive($doc);
    $archive2 = $service->archive($doc);

    // Le même enregistrement doit être retourné
    expect($archive1->id)->toBe($archive2->id);
    expect(DocumentArchive::where('document_id', $doc->id)->count())->toBe(1);
});

it('exports zip with manifest', function () {
    ensureArchiveKeys();
    Storage::fake('local');

    $user    = createUserWithCompanyAndTrial();
    $company = $user->currentCompany;

    $service = app(ArchiveService::class);

    $doc1 = createFinalizedDocument($company);
    $doc2 = createFinalizedDocument($company);
    $service->archive($doc1);
    $service->archive($doc2);

    $zipPath = $service->exportZip($company->id, now()->year);

    expect(file_exists($zipPath))->toBeTrue();

    $zip = new \ZipArchive();
    $zip->open($zipPath);
    $names = [];
    for ($i = 0; $i < $zip->numFiles; $i++) {
        $names[] = $zip->getNameIndex($i);
    }
    $zip->close();

    expect(in_array('manifest.json', $names))->toBeTrue();

    // Nettoyage
    @unlink($zipPath);
});

it('audit trail returns chronological timeline', function () {
    ensureArchiveKeys();
    Storage::fake('local');

    $user    = createUserWithCompanyAndTrial();
    $company = $user->currentCompany;
    $doc     = createFinalizedDocument($company);

    DocumentAuditLog::record($doc, 'created', $user);
    DocumentAuditLog::record($doc, 'finalized', $user);

    $service = app(ArchiveService::class);
    $service->archive($doc);

    $trail = $service->getAuditTrail($doc);

    expect($trail)->toBeArray()->not->toBeEmpty();

    // Verifier l'ordre chronologique
    $dates = array_column($trail, 'occurred_at');
    $sorted = $dates;
    sort($sorted);
    expect($dates)->toBe($sorted);

    // L'entrée d'archivage doit être présente
    $types = array_column($trail, 'type');
    expect(in_array('archive', $types))->toBeTrue();
});

it('isolates archives between companies', function () {
    ensureArchiveKeys();
    Storage::fake('local');

    $user1    = createUserWithCompanyAndTrial();
    $company1 = $user1->currentCompany;

    $user2    = createUserWithCompanyAndTrial();
    $company2 = $user2->currentCompany;

    $doc1 = createFinalizedDocument($company1);
    $doc2 = createFinalizedDocument($company2);

    $service = app(ArchiveService::class);
    $service->archive($doc1);
    $service->archive($doc2);

    $count1 = DocumentArchive::where('company_id', $company1->id)->count();
    $count2 = DocumentArchive::where('company_id', $company2->id)->count();

    expect($count1)->toBe(1);
    expect($count2)->toBe(1);

    // Company 1 ne doit pas voir les archives de company 2
    $archivesForCompany1 = DocumentArchive::where('company_id', $company1->id)->get();
    foreach ($archivesForCompany1 as $a) {
        expect($a->company_id)->toBe($company1->id);
    }
});
