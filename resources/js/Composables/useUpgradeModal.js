import { ref } from 'vue';

const show = ref(false);
const feature = ref('');
const requiredPlan = ref('pro');

export function useUpgradeModal() {
    function openUpgradeModal(featureName, plan = 'pro') {
        feature.value = featureName;
        requiredPlan.value = plan;
        show.value = true;
    }
    function closeUpgradeModal() {
        show.value = false;
    }
    return { show, feature, requiredPlan, openUpgradeModal, closeUpgradeModal };
}
