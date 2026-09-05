import './bootstrap';

import Alpine from 'alpinejs';
import costEstimator from './cost-estimator';
import doctorDirectory from './doctor-directory';
import doctorMatch from './doctor-match';
import hospitalDirectory from './hospital-directory';
import searchSuggest from './search-suggest';

window.Alpine = Alpine;

Alpine.data('doctorDirectory', doctorDirectory);
Alpine.data('doctorMatch', doctorMatch);
Alpine.data('hospitalDirectory', hospitalDirectory);
Alpine.data('costEstimator', costEstimator);
Alpine.data('searchSuggest', searchSuggest);

Alpine.start();
