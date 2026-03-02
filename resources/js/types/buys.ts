// Shared TypeScript definitions for purchases and related entities

export interface Provider {
    id: number;
    id_products: number;
    ruc: string;
    company_name: string;
    contact_person: string;
    phone: number;
    address: string;
    email: string;
    category: string;
    description_products: string;
    status: string;
}

export interface User {
    id: number;
    name: string;
    lastname: string;
    dni: number;
    phone: number;
    address: string;
    email: string;
    children: number;
    affiliate: string;
    insured: string;
    work_modality: string;
    entry_date: string;
    retention: string;
    entry_to_payroll: string;
    role: string;
    state: string;
}

export type PaymentMethod = 'cash' | 'card' | 'yape' | 'plin';
export type PaymentStatus = 'cancelled' | 'pending' | 'delivered';

export interface Buy {
    id: number;
    id_provider: number;
    id_users: number;
    voucher_number: string;
    total: number;
    payment_method: PaymentMethod;
    payment_status: PaymentStatus;
    date_time: string;
    created_at?: string;
    buy_details_count?: number;
    provider?: Pick<Provider, 'id' | 'company_name'>;
    user?: Pick<User, 'id' | 'name' | 'lastname'>;
}

export interface BuyDetail {
    id: number;
    id_buys: number;
    id_products: number;
    quantity: number;
    unit_price: number;
    sub_total: number;
    buy?: Buy;
    product?: Pick<Product, 'id' | 'code' | 'name'>;
}

export interface Product {
    id: number;
    id_categories: number | null;
    code: string;
    name: string;
    description: string;
    unit_price: number;
    higher_price: number | null;
    stock: number;
    expiration_date: string;
    promotion_discount: number;
    state: 'active' | 'inactive';
}
