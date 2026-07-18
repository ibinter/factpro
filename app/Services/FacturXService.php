<?php

namespace App\Services;

use App\Models\Document;

class FacturXService
{
    /**
     * Génère un XML ZUGFeRD 2.1 / Factur-X EN 16931 pour une facture finalisée.
     * Profil MINIMUM (champs obligatoires seulement).
     */
    public function generateXml(Document $document): string
    {
        $document->loadMissing(['company', 'customer', 'payments']);

        $company  = $document->company;
        $customer = $document->customer;

        // TypeCode : 380 = facture, 381 = avoir
        $typeCode = $document->type === 'credit_note' ? '381' : '380';

        // Date au format YYYYMMDD
        $issueDate = $document->issue_date
            ? $document->issue_date->format('Ymd')
            : now()->format('Ymd');

        $subtotal    = number_format((float) $document->subtotal, 2, '.', '');
        $taxAmount   = number_format((float) $document->tax_amount, 2, '.', '');
        $grandTotal  = number_format((float) $document->total, 2, '.', '');
        $amountPaid  = (float) ($document->amount_paid ?? 0);
        $dueAmount   = number_format(max(0, (float) $document->total - $amountPaid), 2, '.', '');

        $currency    = htmlspecialchars($document->currency ?? 'EUR', ENT_XML1);
        $invoiceNum  = htmlspecialchars($document->number ?? '', ENT_XML1);
        $sellerName  = htmlspecialchars($company?->name ?? '', ENT_XML1);
        $countryId   = htmlspecialchars($company?->country ?? 'FR', ENT_XML1);
        $vatNumber   = htmlspecialchars($company?->vat_number ?? '', ENT_XML1);
        $buyerName   = htmlspecialchars($customer?->name ?? '', ENT_XML1);

        $vatBlock = $vatNumber
            ? "<ram:SpecifiedTaxRegistration><ram:ID schemeID=\"VA\">{$vatNumber}</ram:ID></ram:SpecifiedTaxRegistration>"
            : '';

        return <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<rsm:CrossIndustryInvoice
    xmlns:rsm="urn:un:unece:uncefact:data:standard:CrossIndustryInvoice:100"
    xmlns:ram="urn:un:unece:uncefact:data:standard:ReusableAggregateBusinessInformationEntity:100"
    xmlns:udt="urn:un:unece:uncefact:data:standard:UnqualifiedDataType:100">
  <rsm:ExchangedDocumentContext>
    <ram:GuidelineSpecifiedDocumentContextParameter>
      <ram:ID>urn:factur-x.eu:1p0:minimum</ram:ID>
    </ram:GuidelineSpecifiedDocumentContextParameter>
  </rsm:ExchangedDocumentContext>
  <rsm:ExchangedDocument>
    <ram:ID>{$invoiceNum}</ram:ID>
    <ram:TypeCode>{$typeCode}</ram:TypeCode>
    <ram:IssueDateTime>
      <udt:DateTimeString format="102">{$issueDate}</udt:DateTimeString>
    </ram:IssueDateTime>
  </rsm:ExchangedDocument>
  <rsm:SupplyChainTradeTransaction>
    <ram:ApplicableHeaderTradeAgreement>
      <ram:SellerTradeParty>
        <ram:Name>{$sellerName}</ram:Name>
        <ram:PostalTradeAddress><ram:CountryID>{$countryId}</ram:CountryID></ram:PostalTradeAddress>
        {$vatBlock}
      </ram:SellerTradeParty>
      <ram:BuyerTradeParty>
        <ram:Name>{$buyerName}</ram:Name>
      </ram:BuyerTradeParty>
    </ram:ApplicableHeaderTradeAgreement>
    <ram:ApplicableHeaderTradeDelivery/>
    <ram:ApplicableHeaderTradeSettlement>
      <ram:InvoiceCurrencyCode>{$currency}</ram:InvoiceCurrencyCode>
      <ram:SpecifiedTradeSettlementHeaderMonetarySummation>
        <ram:LineTotalAmount>{$subtotal}</ram:LineTotalAmount>
        <ram:TaxTotalAmount>{$taxAmount}</ram:TaxTotalAmount>
        <ram:GrandTotalAmount>{$grandTotal}</ram:GrandTotalAmount>
        <ram:DuePayableAmount>{$dueAmount}</ram:DuePayableAmount>
      </ram:SpecifiedTradeSettlementHeaderMonetarySummation>
    </ram:ApplicableHeaderTradeSettlement>
  </rsm:SupplyChainTradeTransaction>
</rsm:CrossIndustryInvoice>
XML;
    }

    /** Retourne le nom de fichier réglementaire : "factur-x.xml" */
    public function fileName(Document $document): string
    {
        return 'factur-x.xml';
    }
}
