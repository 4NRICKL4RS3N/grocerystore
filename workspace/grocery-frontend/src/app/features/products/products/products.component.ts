import { CommonModule } from '@angular/common';
import { Component, OnInit } from '@angular/core';
import { FormsModule } from '@angular/forms';

interface Product {
	id?: number;
	name: string;
	sku?: string;
	barcode?: string;
	category?: string;
	unit?: string;
	price_mga: number;
	cost_mga?: number;
	stock_quantity?: number;
	is_active?: boolean;
}

@Component({
  selector: 'app-products',
  standalone: true,
  imports: [CommonModule, FormsModule],
  template: `
    <div style="max-width: 900px; margin: 0 auto; padding: 16px;">
      <h2 style="font-size: 28px; margin-bottom: 16px;">Products</h2>

      <form (ngSubmit)="save()" style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; align-items: end; margin-bottom: 16px;">
        <div>
          <label style="font-size: 18px;">Name</label>
          <input [(ngModel)]="form.name" name="name" required style="width: 100%; padding: 12px; font-size: 18px;" />
        </div>
        <div>
          <label style="font-size: 18px;">Price (MGA)</label>
          <input type="number" [(ngModel)]="form.price_mga" name="price_mga" required min="0" style="width: 100%; padding: 12px; font-size: 18px;" />
        </div>
        <div>
          <label style="font-size: 18px;">SKU</label>
          <input [(ngModel)]="form.sku" name="sku" style="width: 100%; padding: 12px; font-size: 18px;" />
        </div>
        <div>
          <label style="font-size: 18px;">Barcode</label>
          <input [(ngModel)]="form.barcode" name="barcode" style="width: 100%; padding: 12px; font-size: 18px;" />
        </div>
        <div>
          <button type="submit" style="grid-column: 1 / -1; padding: 14px; font-size: 20px; background: #0d6efd; color: #fff; border: none; border-radius: 8px;">{{ form.id ? 'Update' : 'Add' }} Product</button>
        </div>
      </form>

      <div *ngFor="let p of products" style="display:flex; justify-content: space-between; align-items: center; padding: 14px; border: 1px solid #ddd; border-radius: 8px; margin-bottom: 10px;">
        <div>
          <div style="font-size: 20px; font-weight: 600;">{{ p.name }}</div>
          <div style="font-size: 16px; color:#555;">{{ p.sku || '-' }} • {{ p.barcode || '-' }}</div>
        </div>
        <div style="font-size: 20px;">{{ p.price_mga | number:'1.0-0' }} MGA</div>
        <div>
          <button (click)="edit(p)" style="padding: 10px 14px; font-size: 18px; margin-right:8px;">Edit</button>
          <button (click)="remove(p)" style="padding: 10px 14px; font-size: 18px; background:#dc3545; color:#fff; border:none; border-radius:6px;">Delete</button>
        </div>
      </div>
    </div>
  `,
  styles: ``
})
export class ProductsComponent implements OnInit {
  products: Product[] = [];
  form: Product = { name: '', price_mga: 0 };
  private baseUrl = '/api';

  async ngOnInit() {
    await this.load();
  }

  async load() {
    const res = await fetch(`${this.baseUrl}/products`);
    const data = await res.json();
    this.products = data.data ?? data;
  }

  edit(p: Product) {
    this.form = { ...p };
  }

  async save() {
    const isUpdate = !!this.form.id;
    const url = isUpdate ? `${this.baseUrl}/products/${this.form.id}` : `${this.baseUrl}/products`;
    const method = isUpdate ? 'PUT' : 'POST';
    await fetch(url, {
      method,
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(this.form)
    });
    this.form = { name: '', price_mga: 0 };
    await this.load();
  }

  async remove(p: Product) {
    if (!confirm('Delete this product?')) return;
    await fetch(`${this.baseUrl}/products/${p.id}`, { method: 'DELETE' });
    await this.load();
  }
}
