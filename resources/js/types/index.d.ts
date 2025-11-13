import type { LucideIcon } from 'lucide-vue-next';
import type { Config } from 'ziggy-js';

export interface Auth {
    user: User;
}

export interface BreadcrumbItem {
    title: string;
    href: string;
}

export interface NavItem {
    title: string;
    href: string;
    icon?: LucideIcon;
    isActive?: boolean;
    access: boolean;
}

export type AppPageProps<T extends Record<string, unknown> = Record<string, unknown>> = T & {
    name: string;
    quote: { message: string; author: string };
    auth: Auth;
    flash?: {
        success: string;
        error: string;
        info: string;
    };
    ziggy: Config & { location: string };
    sidebarOpen: boolean;
};

export type BreadcrumbItemType = BreadcrumbItem;

/*
|------------------------------------------------------------------------------
| Resources/Formatters
|------------------------------------------------------------------------------
*/
export interface PaginationMetadata {
    path: string;
    current_page: number;
    per_page: number;
    last_page: number;
    total: number;
    from_index: number | null;
    to_index: number | null;
}

export interface PaginatedData<T> {
    data: T[];
    meta: PaginationMetadata;
}

export interface DateTimeResource {
    human: string;
    date_time: string;
    formatted_date: string;
}

export interface DateResource {
    human: string;
    date_time: string;
    formatted_date: string;
}

export interface AgeResource {
    years: number;
    months: number;
    days: number;
    formatted_short: string;
    formatted_long: string;
}

/*
|------------------------------------------------------------------------------
| Data resource types
|------------------------------------------------------------------------------
*/
export interface Notification {
    id: string;
    message: string;
    link: string;
    read_at: DateTimeResource | null;
    created_at: DateTimeResource;
}

export interface User {
    id: number;
    first_name: string;
    last_name: string;
    middle_name: string | null;
    email: string;
    email_verified_at: string | null;
    avatar?: string;
    is_active: boolean;
    role: 'admin' | 'system_admin' | 'doctor' | 'laboratory_staff' | 'cashier' | 'patient';
    permissions: string[];
    notifications: Notification[];
    notifications_unread_count: number;

    created_at: DateTimeResource;
    updated_at: DateTimeResource;
}

export interface Patient {
    id: number;
    user_id: number | null;
    first_name: string;
    last_name: string;
    middle_name: string | null;
    gender: 'male' | 'female';
    civil_status: 'single' | 'married' | 'widowed' | 'divorced' | 'separated';
    birthdate: DateTime;
    age: AgeResource;
    contact_number: string;
    address: string;
    deleted_at: DateTimeResource | null;
    created_at: DateTimeResource;
    updated_at: DateTimeResource;

    user?: User;
    appointments?: Appointment[];
    invoices?: Invoice[];
    consultations?: Consultation[];
    laboratory_results?: LaboratoryResult[];
}

export interface Appointment {
    id: number;
    patient_id: number;
    complaints: string | null;
    type:
        | 'consultation'
        | 'family_planning_counseling'
        | 'natural_methods'
        | 'chelation_therapy'
        | 'magnetic_resonance_analysis'
        | 'multifunctional_high_potential_therapeutic_services'
        | 'weight_loss_management'
        | 'psychosocial_and_spiritual_counseling'
        | 'pregnancy_test'
        | 'papsmear'
        | 'cbc'
        | 'urinalysis'
        | 'fecalysis'
        | 'pre_natal_and_post_natal'
        | 'normal_spontaneous_delivery'
        | 'immunization'
        | 'ear_pearcing'
        | 'nebulization'
        | 'foley_catheter_insertion'
        | 'surgical_wound_dressing'
        | 'cord_dressing'
        | 'suture_removal'
        | 'issuance_of_bc_newborn_screening'
        | 'general_opd_consultation'
        | 'medical_opd_consultation'
        | 'minor_surgical_procedures'
        | 'issuance_of_medical_certificate'
        | 'pedia_adult_vaccination_services';
    status: 'pending' | 'approved' | 'rejected' | 'cancelled' | 'completed';
    is_reschedulable: boolean;
    is_cancellable: boolean;
    is_operatable: boolean;
    scheduled_at: DateTimeResource;
    deleted_at: DateTimeResource | null;
    created_at: DateTimeResource;
    updated_at: DateTimeResource;

    patient?: Patient;
    invoice?: Invoice;
    consultations?: Consultation[];
    laboratory_results?: LaboratoryResult[];
    activities?: Activity[];
}

export interface Procedure {
    id: number;
    appointment_id: number;
    description: string;
    quantity: number;
    created_at: DateTimeResource;
    updated_at: DateTimeResource;

    appointment?: Appointment;
}

export interface Consultation {
    id: number;
    appointment_id: number;
    type:
        | 'consultation'
        | 'family_planning_counseling'
        | 'natural_methods'
        | 'chelation_therapy'
        | 'magnetic_resonance_analysis'
        | 'multifunctional_high_potential_therapeutic_services'
        | 'weight_loss_management'
        | 'psychosocial_and_spiritual_counseling'
        | 'pre_natal_and_post_natal'
        | 'normal_spontaneous_delivery'
        | 'immunization'
        | 'ear_pearcing'
        | 'nebulization'
        | 'foley_catheter_insertion'
        | 'surgical_wound_dressing'
        | 'cord_dressing'
        | 'suture_removal'
        | 'issuance_of_bc_newborn_screening'
        | 'general_opd_consultation'
        | 'medical_opd_consultation'
        | 'minor_surgical_procedures'
        | 'issuance_of_medical_certificate'
        | 'pedia_adult_vaccination_services';
    status: 'pending' | 'approved' | 'rejected' | 'cancelled' | 'completed';
    chief_complaints: string;
    assessment: string;
    plan: string;

    // Vital signs
    systolic: number | null;
    diastolic: number | null;
    heart_rate: number | null;
    respiratory_rate: number | null;
    weight_kg: number | null;
    height_cm: number | null;
    bmi: number | null;
    temperature_c: number | null;
    oxygen_saturation: number | null;
    created_at: DateTimeResource;
    updated_at: DateTimeResource;

    appointment?: Appointment;
}

export interface LaboratoryResult {
    id: number;
    appointment_id: number;
    description: string | null;
    type: 'pregnancy_test' | 'papsmear' | 'cbc' | 'urinalysis' | 'fecalysis';
    status: 'pending' | 'released';
    results_file_path: string | null;
    results_file_url: string | null;
    created_at: DateTimeResource;
    updated_at: DateTimeResource;

    appointment?: Appointment;
}

export interface Invoice {
    id: number;
    appointment_id: number;
    total: number;
    total_paid: number;
    balance: number;
    status: 'unpaid' | 'partially_paid' | 'paid' | 'cancelled';
    created_at: DateTimeResource;
    updated_at: DateTimeResource;

    appointment?: Appointment;
    invoice_items?: InvoiceItem[];
    payments?: Payment[];
}

export interface InvoiceItem {
    id: number;
    invoice_id: number;
    description: string;
    quantity: number;
    unit_price: number;
    line_total: number;

    invoice?: Invoice;
}

export interface Payment {
    id: number;
    amount: number;
    created_at: DateTimeResource;
    updated_at: DateTimeResource;

    invoice?: Invoice;
}

export interface Activity {
    id: number;
    description: string;
    event: string;
    properties: any;
    causer: User | null;
    created_at: DateTimeResource;
}
