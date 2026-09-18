import './bootstrap';

import Alpine from 'alpinejs';
import costEstimator from './cost-estimator';
import doctorDirectory from './doctor-directory';
import doctorMatch from './doctor-match';
import hospitalDirectory from './hospital-directory';
import searchSuggest from './search-suggest';

window.Alpine = Alpine;
window.costEstimator = costEstimator;
window.doctorDirectory = doctorDirectory;
window.doctorMatch = doctorMatch;
window.hospitalDirectory = hospitalDirectory;
window.searchSuggest = searchSuggest;

const registerAlpineData = () => {
    Alpine.data('doctorDirectory', doctorDirectory);
    Alpine.data('doctorMatch', doctorMatch);
    Alpine.data('hospitalDirectory', hospitalDirectory);
    Alpine.data('costEstimator', costEstimator);
    Alpine.data('searchSuggest', searchSuggest);
};

document.addEventListener('alpine:init', registerAlpineData);
registerAlpineData();

Alpine.start();

