import './bootstrap';

import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.start();

import { TabulatorFull as Tabulator } from 'tabulator-tables';
window.Tabulator = Tabulator;

// استيراد ملف CSS الخاص بـ Tabulator
import 'tabulator-tables/dist/css/tabulator.min.css';