import { Routes } from '@angular/router';

export const routes: Routes = [
	{ path: '', redirectTo: 'dashboard', pathMatch: 'full' },
	{ path: 'dashboard', loadComponent: () => import('./features/dashboard/dashboard.component').then(m => m.DashboardComponent) },
	{ path: 'products', loadComponent: () => import('./features/products/products.component').then(m => m.ProductsComponent) },
	{ path: 'stock', loadComponent: () => import('./features/stock/stock.component').then(m => m.StockComponent) },
	{ path: 'sales', loadComponent: () => import('./features/sales/sales.component').then(m => m.SalesComponent) },
	{ path: 'analytics', loadComponent: () => import('./features/analytics/analytics.component').then(m => m.AnalyticsComponent) },
];
