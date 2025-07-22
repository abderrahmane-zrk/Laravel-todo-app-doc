import './bootstrap';
import 'tabulator-tables/dist/css/tabulator.min.css';

// ✅ الاستيراد الصحيح حسب طريقة المكتبة (Named Export)
import { TabulatorFull as Tabulator } from 'tabulator-tables';

window.Tabulator = Tabulator;