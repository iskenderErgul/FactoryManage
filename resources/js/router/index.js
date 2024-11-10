import { createRouter, createWebHistory } from 'vue-router';
import AppLayout from "@/layout/AppLayout.vue";
import HomeView from "@/views/HomeView.vue";
import Login from "@/pages/Login.vue";
import store from "@/store/index.js";
import UserManagement from "@/components/UserManagements/UserManagement.vue";
import UserLog from "@/components/UserManagements/UserLog.vue";
import PacsEntries from "@/components/PacsManagment/PacsEntries.vue";
import PacsEntriesLog from "@/components/PacsManagment/PacsEntriesLog.vue";
import ShiftTemplates from "@/components/ShiftManagement/ShiftTemplates.vue";
import Shifts from "@/components/ShiftManagement/Shifts.vue";
import Production from "@/components/ProductionManagement/Production.vue";
import ProductionLog from "@/components/ProductionManagement/ProductionLog.vue";
import Machines from "@/components/ProductionManagement/Machines.vue";
import Costs from "@/components/ProductionManagement/Costs.vue";
import Product from "@/components/StockManagement/Product.vue";
import StockMovements from "@/components/StockManagement/StockMovements.vue";
import StockMovementsLog from "@/components/StockManagement/StockMovementsLog.vue";
import Sales from "@/components/SalesManagement/Sales.vue";
import SalesLog from "@/components/SalesManagement/SalesLog.vue";
import Customers from "@/components/SalesManagement/Customers.vue";
import ProductionReports from "@/components/Reports/ProductionReports.vue";
import SalesReports from "@/components/Reports/SalesReports.vue";
import StockReports from "@/components/Reports/StockReports.vue";
import FinancialReports from "@/components/Reports/FinancialReports.vue";
import ShiftAssignments from "@/components/ShiftManagement/ShiftAssignments.vue";


const routes = [

    {
        path : '/sys',
        component :AppLayout,
        children :  [
            {
                path : '/sys',
                component :HomeView,
                name : 'home'
            },
            //UserManagement
            {
                path : '/sys/manage-users',
                name : 'userManagement',
                component : UserManagement
            },
            {
                path : '/sys/user-log',
                name : 'userLog',
                component : UserLog
            },

// Pacs Management
            {
                path : '/sys/pacs-entries',
                name : 'pacsEntries',
                component : PacsEntries
            },
            {
                path : '/sys/pacs-log',
                name : 'pacsEntriesLog',
                component : PacsEntriesLog
            },

// Shift Management
            {
                path : '/sys/shift-templates',
                name : 'shiftTemplates',
                component : ShiftTemplates
            },
            {
                path : '/sys/shift-assignments',
                name : 'shiftAssignments',
                component : ShiftAssignments
            },
            {
                path : '/sys/shifts',
                name : 'shifts',
                component : Shifts
            },


// Production Management
            {
                path : '/sys/production',
                name : 'production',
                component : Production
            },
            {
                path : '/sys/production-log',
                name : 'productionLog',
                component : ProductionLog
            },
            {
                path : '/sys/machine-management',
                name : 'machineManagement',
                component : Machines
            },
            {
                path : '/sys/cost-management',
                name : 'costManagement',
                component : Costs
            },

// Stock Management
            {
                path : '/sys/product-management',
                name : 'productManagement',
                component : Product
            },
            {
                path : '/sys/stock-movements',
                name : 'stockMovements',
                component : StockMovements
            },
            {
                path : '/sys/stock-movements-log',
                name : 'stockMovementsLog',
                component : StockMovementsLog
            },

// Sales Management
            {
                path : '/sys/sales',
                name : 'sales',
                component : Sales
            },
            {
                path : '/sys/sales-log',
                name : 'salesLog',
                component : SalesLog
            },
            {
                path : '/sys/customers',
                name : 'customers',
                component : Customers
            },

// Reports
            {
                path : '/sys/reports/production',
                name : 'productionReports',
                component : ProductionReports
            },
            {
                path : '/sys/reports/sales',
                name : 'salesReport',
                component : SalesReports
            },
            {
                path : '/sys/reports/stocks',
                name : 'stocksReport',
                component : StockReports
            },
            {
                path : '/sys/reports/financial',
                name : 'financialReport',
                component : FinancialReports
            },

        ]
    },
    {
        path: '/login',
        component:Login,
        name : 'login',

    },


];

const router = createRouter({
    history: createWebHistory(),
    routes,
});
router.beforeEach(async (to, from, next) => {
    try {
        await store.dispatch('authenticate');

        // Eğer kullanıcı zaten authenticated ise ve login sayfasına gitmeye çalışıyorsa
        if (store.getters.authenticated && to.name === 'login') {
            // Ana sayfaya veya başka bir sayfaya yönlendirebilirsiniz
            next({ name: 'home' }); // 'home' sayfasına yönlendirin
        } else if (store.getters.authenticated || to.name === 'login') {
            next();
        } else {
            next({ name: 'login' });
        }
    } catch (error) {
        console.error('Authentication error:', error);
        next({ name: 'login' });
    }
});

export default router;
