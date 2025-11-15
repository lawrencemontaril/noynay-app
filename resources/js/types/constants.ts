import { BadgeVariants } from '@/components/ui/badge';
import { Appointment, Invoice } from '.';

export const APPOINTMENT_STATUSES: {
    label: string;
    value: Appointment['status'];
    badge: BadgeVariants['variant'];
}[] = [
    { label: 'Pending', value: 'pending', badge: 'warning' },
    { label: 'Approved', value: 'approved', badge: 'default' },
    { label: 'Completed', value: 'completed', badge: 'default' },
    { label: 'Rejected', value: 'rejected', badge: 'destructive' },
    { label: 'Cancelled', value: 'cancelled', badge: 'destructive' },
    { label: 'No Show', value: 'no_show', badge: 'destructive' },
];

export const INVOICE_STATUSES: {
    label: string;
    value: Invoice['status'];
    badge: BadgeVariants['variant'];
}[] = [
    { label: 'Unpaid', value: 'unpaid', badge: 'warning' },
    { label: 'Paid', value: 'paid', badge: 'default' },
    { label: 'Partially Paid', value: 'partially_paid', badge: 'warning' },
    { label: 'Cancelled', value: 'cancelled', badge: 'destructive' },
];

export const LAB_TYPES = [
    { label: 'Pregnancy Test', value: 'pregnancy_test' },
    { label: 'Papsmear', value: 'papsmear' },
    { label: 'Complete Blood Count', value: 'cbc' },
    { label: 'Urinalysis', value: 'urinalysis' },
    { label: 'Fecalysis', value: 'fecalysis' },
];

export const CONSULTATION_TYPES = [
    {
        label: 'Consultation',
        value: 'consultation',
    },
    { label: 'Family Planning Counseling', value: 'family_planning_counseling' },
    { label: 'Natural Methods (Rhythm), Pills, Depotrust', value: 'natural_methods' },
    { label: 'Chelation Therapy', value: 'chelation_therapy' },
    { label: 'Magnetic Resonance Analysis', value: 'magnetic_resonance_analysis' },
    {
        label: 'Multifunctional High Potential Therapeutic Services',
        value: 'multifunctional_high_potential_therapeutic_services',
    },
    { label: 'Weight Loss Management', value: 'weight_loss_management' },
    {
        label: 'Psychosocial and Spiritual Counseling',
        value: 'psychosocial_and_spiritual_counseling',
    },
    { label: 'Pre-Natal and Post-Natal Check Up', value: 'pre_natal_and_post_natal' },
    { label: 'Normal Spontaneous Delivery', value: 'normal_spontaneous_delivery' },
    { label: 'Immunization - BCG, HEP. B Vaccines, etc.', value: 'immunization' },
    { label: 'Ear Piercing With Hypoallergenic Earrings', value: 'ear_pearcing' },
    { label: 'Nebulization With and Without Medication', value: 'nebulization' },
    { label: 'Foley Cathether Insertion', value: 'foley_catheter_insertion' },
    { label: 'Surgical Wound Dressing', value: 'surgical_wound_dressing' },
    { label: 'Cord Dressing', value: 'cord_dressing' },
    { label: 'Suture Removal', value: 'suture_removal' },
    {
        label: 'Issuance of Birth Certificate; Newborn Screening',
        value: 'issuance_of_bc_newborn_screening',
    },
    { label: 'General OPD Consultation', value: 'general_opd_consultation' },
    {
        label: 'Medical / OPD / Pre-Employment Consultations',
        value: 'medical_opd_consultation',
    },
    { label: 'Minor Surgical Procedures', value: 'minor_surgical_procedures' },
    {
        label: 'Issuance of Medical Certificate',
        value: 'issuance_of_medical_certificate',
    },
    {
        label: 'Pedia / Adult Immunization / Vaccination Services',
        value: 'pedia_adult_vaccination_services',
    },
];

export const ALL_SERVICES = [...CONSULTATION_TYPES, ...LAB_TYPES];

export const USER_ROLES = [
    {
        label: 'Administrator',
        value: 'admin',
    },
    {
        label: 'System Administrator',
        value: 'system_admin',
    },
    {
        label: 'Doctor',
        value: 'doctor',
    },
    {
        label: 'Laboratory Staff',
        value: 'laboratory_staff',
    },
    {
        label: 'Cashier',
        value: 'cashier',
    },
    {
        label: 'Patient',
        value: 'patient',
    },
];

export const PATIENT_GENDERS = [
    {
        label: 'Male',
        value: 'male',
    },
    {
        label: 'Female',
        value: 'female',
    },
];
