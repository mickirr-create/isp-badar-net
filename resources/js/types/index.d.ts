export interface User {
    id: number;
    username: string;
    fullname?: string;
    email?: string;
    phone?: string;
    status: string;
    user_type?: string;
    created_at: string;
    updated_at: string;
}

export interface Customer {
    id: number;
    username: string;
    fullname?: string;
    email?: string;
    phonenumber?: string;
    status: string;
    service_type?: string;
    balance?: number;
    address?: string;
    city?: string;
    district?: string;
    state?: string;
    zip?: string;
    coordinates?: string;
    account_type?: string;
    auto_renewal?: boolean;
    last_login?: string;
    created_at: string;
    updated_at: string;
}

export interface Admin extends User {
    last_login?: string;
    user_type: string;
}

export interface Plan {
    id: number;
    name_plan: string;
    id_bw?: number;
    price: string;
    price_old?: string;
    type: string;
    typebp?: string;
    limit_type?: string;
    time_limit?: number;
    time_unit?: string;
    data_limit?: number;
    data_unit?: string;
    validity: number;
    validity_unit: string;
    shared_users?: number;
    routers: string;
    is_radius?: boolean;
    pool?: string;
    plan_expired?: number;
    expired_date?: number;
    enabled?: boolean;
    prepaid?: string;
    plan_type?: string;
    device?: string;
    created_at: string;
    updated_at: string;
}

export interface Router {
    id: number;
    name: string;
    ip_address: string;
    username: string;
    password: string;
    description?: string;
    coordinates?: string;
    status?: string;
    last_seen?: string;
    coverage?: string;
    enabled?: boolean;
    created_at: string;
    updated_at: string;
}

export interface UserRecharge {
    id: number;
    customer_id: number;
    username: string;
    plan_id: number;
    namebp: string;
    recharged_on: string;
    recharged_time: string;
    expiration: string;
    time: string;
    status: string;
    method: string;
    routers: string;
    type: string;
    admin_id: number;
}

export interface Transaction {
    id: number;
    invoice: string;
    username: string;
    user_id: number;
    plan_name: string;
    price: string;
    recharged_on: string;
    recharged_time: string;
    expiration: string;
    time: string;
    method: string;
    routers: string;
    type: string;
    note: string;
    admin_id: number;
}

export interface Voucher {
    id: number;
    type: string;
    routers: string;
    id_plan: number;
    code: string;
    user: string;
    status: string;
    created_at: string;
    used_date?: string;
    generated_by: number;
}

export interface Bandwidth {
    id: number;
    name_bw: string;
    rate_down: number;
    rate_down_unit: string;
    rate_up: number;
    rate_up_unit: string;
    burst: string;
}

export interface App {
    user?: Admin | Customer | null;
    guard?: string | null;
    flash?: {
        message?: string;
        error?: string;
        success?: string;
    };
}

declare global {
    interface Window {
        axios: typeof import('axios').default;
    }
}

declare module '@inertiajs/react' {
    interface PageProps {
        app: App;
        [key: string]: unknown;
    }
}
