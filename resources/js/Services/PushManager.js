/**
 * IBIG FactPro — Push Notifications Manager (Phase 16).
 * Gère l'abonnement / désabonnement aux push notifications Web.
 */
export class PushManager {
    static async isSupported() {
        return (
            'serviceWorker' in navigator &&
            'PushManager' in window &&
            'Notification' in window
        );
    }

    static async getPermission() {
        // 'default' | 'granted' | 'denied'
        return Notification.permission;
    }

    static async subscribe() {
        const reg = await navigator.serviceWorker.ready;

        const resp = await fetch('/push/vapid-public-key');
        const { public_key } = await resp.json();

        const subscription = await reg.pushManager.subscribe({
            userVisibleOnly: true,
            applicationServerKey: PushManager._urlBase64ToUint8Array(public_key),
        });

        const csrfToken =
            document.querySelector('meta[name=csrf-token]')?.content ?? '';

        await fetch('/push/subscribe', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
            },
            body: JSON.stringify(subscription.toJSON()),
        });

        return subscription;
    }

    static async unsubscribe() {
        const reg = await navigator.serviceWorker.ready;
        const subscription = await reg.pushManager.getSubscription();

        if (!subscription) return false;

        const csrfToken =
            document.querySelector('meta[name=csrf-token]')?.content ?? '';

        await fetch('/push/unsubscribe', {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
            },
            body: JSON.stringify({ endpoint: subscription.endpoint }),
        });

        await subscription.unsubscribe();
        return true;
    }

    /**
     * Conversion base64url → Uint8Array (requis par Web Push API).
     */
    static _urlBase64ToUint8Array(base64String) {
        const padding = '='.repeat((4 - (base64String.length % 4)) % 4);
        const base64 = (base64String + padding)
            .replace(/-/g, '+')
            .replace(/_/g, '/');

        const rawData = window.atob(base64);
        const outputArray = new Uint8Array(rawData.length);

        for (let i = 0; i < rawData.length; ++i) {
            outputArray[i] = rawData.charCodeAt(i);
        }

        return outputArray;
    }
}
