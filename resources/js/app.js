import Alpine from 'alpinejs';
import coverPicker from './cover-picker';

window.Alpine = Alpine;

Alpine.data('coverPicker', coverPicker);

Alpine.start();
