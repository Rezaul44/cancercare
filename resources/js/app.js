import './bootstrap';

import Alpine from 'alpinejs';
import doctorDirectory from './doctor-directory';
import doctorMatch from './doctor-match';

window.Alpine = Alpine;

Alpine.data('doctorDirectory', doctorDirectory);
Alpine.data('doctorMatch', doctorMatch);

Alpine.start();
