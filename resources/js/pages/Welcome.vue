<script setup lang="ts">
import Button from '@/components/ui/button/Button.vue';
import LandingLayout from '@/layouts/LandingLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { Facebook, Mail, Phone } from 'lucide-vue-next';
import { onMounted, onUnmounted, ref } from 'vue';

const services = {
    consultation: {
        label: 'Consultation',
        children: [{ label: 'Consultation', value: 'consultation' }],
    },
    family_planning_service: {
        label: 'Family Planning Services',
        children: [
            { label: 'Family Planning Counseling', value: 'family_planning_counseling' },
            { label: 'Natural Methods (Rhythm), Pills, Depotrust', value: 'natural_methods' },
        ],
    },
    integrative_and_wellness: {
        label: 'Integrative and Wellness Healthcare Services',
        children: [
            { label: 'Chelation Therapy', value: 'chelation_therapy' },
            { label: 'Magnetic Resonance Analysis', value: 'magnetic_resonance_analysis' },
            {
                label: 'Multifunctional High Potential Therapeutic Services',
                value: 'multifunctional_high_potential_therapeutic_services',
            },
            { label: 'Weight Loss Management', value: 'weight_loss_management' },
            { label: 'Psychosocial and Spiritual Counseling', value: 'psychosocial_and_spiritual_counseling' },
        ],
    },
    laboratory_services: {
        label: 'Laboratory Services',
        children: [
            { label: 'Pregnancy Test', value: 'pregnancy_test' },
            { label: 'Papsmear', value: 'papsmear' },
            { label: 'Complete Blood Count', value: 'cbc' },
            { label: 'Urinalysis', value: 'urinalysis' },
            { label: 'Fecalysis', value: 'fecalysis' },
        ],
    },
    maternal_and_child_health_services: {
        label: 'Maternal and Child Health Services',
        children: [
            { label: 'Pre-Natal and Post-Natal Check Up', value: 'pre_natal_and_post_natal' },
            { label: 'Normal Spontaneous Delivery', value: 'normal_spontaneous_delivery' },
            { label: 'Immunization - BCG, HEP. B Vaccines, etc.', value: 'immunization' },
            { label: 'Ear Piercing With Hypoallergenic Earrings', value: 'ear_pearcing' },
            { label: 'Nebulization With and Without Medication', value: 'nebulization' },
            { label: 'Foley Catheter Insertion', value: 'foley_catheter_insertion' },
            { label: 'Surgical Wound Dressing', value: 'surgical_wound_dressing' },
            { label: 'Cord Dressing', value: 'cord_dressing' },
            { label: 'Suture Removal', value: 'suture_removal' },
            { label: 'Issuance of Birth Certificate; Newborn Screening', value: 'issuance_of_bc_newborn_screening' },
        ],
    },
    medical_surgical_services: {
        label: 'Medical/Surgical Services',
        children: [
            { label: 'General OPD Consultation', value: 'general_opd_consultation' },
            { label: 'Medical / OPD / Pre-Employment Consultations', value: 'medical_opd_consultation' },
            { label: 'Minor Surgical Procedures', value: 'minor_surgical_procedures' },
            { label: 'Issuance of Medical Certificate', value: 'issuance_of_medical_certificate' },
            { label: 'Pedia / Adult Immunization / Vaccination Services', value: 'pedia_adult_vaccination_services' },
        ],
    },
};

type ServiceKey = keyof typeof services;

const categoryRefs = ref<Record<ServiceKey, HTMLElement | null>>({
    consultation: null,
    family_planning_service: null,
    integrative_and_wellness: null,
    laboratory_services: null,
    maternal_and_child_health_services: null,
    medical_surgical_services: null,
});

const scrollToCategory = (key: ServiceKey) => {
    const element = categoryRefs.value[key];
    if (element) element.scrollIntoView({ behavior: 'smooth', block: 'start' });
};

const setCategoryRef = (key: ServiceKey, el: HTMLElement | null) => {
    categoryRefs.value[key] = el;
};

const activeSection = ref<ServiceKey | null>(null);
let observer: IntersectionObserver;

onMounted(() => {
    const sections = Object.entries(categoryRefs.value)
        .map(([key, el]) => ({ key: key as ServiceKey, el }))
        .filter((s) => s.el !== null) as { key: ServiceKey; el: HTMLElement }[];

    observer = new IntersectionObserver(
        () => {
            let closestSection: { key: ServiceKey; distance: number } | null = null;

            sections.forEach(({ key, el }) => {
                const rect = el.getBoundingClientRect();
                const distance = Math.abs(rect.top); // distance from top of viewport

                if (!closestSection || distance < closestSection.distance) {
                    closestSection = { key, distance };
                }
            });

            if (closestSection) {
                activeSection.value = closestSection.key;
            }
        },
        { threshold: [0, 0.25, 0.5, 0.75, 1] },
    );

    sections.forEach(({ el }) => observer.observe(el));
});

onUnmounted(() => {
    if (observer) observer.disconnect();
});
</script>

<template>
    <Head title="Home" />

    <LandingLayout>
        <!-- Hero Section -->
        <section>
            <img
                src="/images/noynay_medical_center_banner.jpg"
                class="object-cover md:h-96 md:w-full"
            />

            <div class="bg-primary/15 py-8 text-center">
                <h1 class="mb-4 text-4xl font-bold">We serve to make you better.</h1>
                <p class="mb-6 text-lg text-zinc-700">
                    Book appointments, manage billing, and access healthcare services anytime, anywhere.
                </p>

                <Button
                    size="lg"
                    as-child
                >
                    <Link
                        :href="route('register')"
                        prefetch
                    >
                        Get Started
                    </Link>
                </Button>
            </div>
        </section>

        <!-- Services Section -->
        <section class="mt-12 flex px-4">
            <!-- Navigation Sidebar -->
            <nav class="sticky top-20 hidden h-[80vh] w-64 overflow-auto pr-4 md:block">
                <h2 class="mb-4 text-xl font-bold">Our services</h2>
                <ul>
                    <li
                        v-for="(service, key) in services"
                        :key="key"
                        class="mb-2"
                    >
                        <Button
                            class="h-fit w-full wrap-break-word whitespace-normal"
                            @click="scrollToCategory(key)"
                            :variant="activeSection === key ? 'default' : 'ghost'"
                        >
                            {{ service.label }}
                        </Button>
                    </li>
                </ul>
            </nav>

            <!-- Services List -->
            <div class="flex-1 space-y-12">
                <div
                    v-for="(service, key) in services"
                    :key="key"
                    :ref="(el) => setCategoryRef(key as ServiceKey, el as HTMLElement | null)"
                    :data-key="key"
                    class="scroll-m-16"
                >
                    <h3 class="sticky top-0 z-10 border-b border-zinc-200 bg-white px-2 py-3 text-2xl font-bold">
                        {{ service.label }}
                    </h3>

                    <div class="mt-4 grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                        <div
                            v-for="child in service.children"
                            :key="child.value"
                            class="overflow-hidden rounded-lg bg-white shadow-md"
                        >
                            <img
                                :src="`/images/services/${child.value}.jpg`"
                                :alt="child.label"
                                class="h-48 w-full object-cover"
                            />
                            <div class="p-4">
                                <h4 class="mb-2 text-lg font-semibold">{{ child.label }}</h4>
                                <p class="text-sm text-zinc-600">
                                    Comprehensive care and expert attention to support your health and wellness.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Call to Action -->
        <section class="mt-16 bg-zinc-50 py-16 text-center">
            <h2 class="mb-4 text-2xl font-bold">Why Choose Us?</h2>
            <p class="mx-auto mb-6 max-w-2xl text-zinc-700">
                Our clinic combines advanced medical technology with personalized care. Manage appointments, track
                billing, and access specialized services all in one place—designed for your convenience.
            </p>

            <Button
                size="lg"
                as-child
            >
                <Link
                    :href="route('register')"
                    prefetch
                >
                    Book Your Appointment
                </Link>
            </Button>
        </section>

        <!-- Footer -->
        <footer class="bg-zinc-900 py-10 text-zinc-300">
            <div class="mx-auto flex max-w-6xl flex-col justify-between gap-10 px-6 md:flex-row">
                <!-- Left: Branding -->
                <div>
                    <h3 class="text-xl font-bold text-white">Noynay Medical Center</h3>
                    <p class="mt-2 max-w-sm text-zinc-400">
                        Compassionate care, modern facilities, and health services you can trust.
                    </p>
                </div>

                <!-- Middle: Navigation -->
                <div class="grid grid-cols-2 gap-8 text-sm md:grid-cols-3">
                    <div>
                        <h4 class="mb-3 font-semibold text-white">Services</h4>
                        <ul class="space-y-2">
                            <li>
                                <Link
                                    :href="route('register')"
                                    class="hover:text-white"
                                    >Book Appointment</Link
                                >
                            </li>
                            <li>
                                <Link
                                    :href="route('login')"
                                    class="hover:text-white"
                                    >Patient Portal</Link
                                >
                            </li>
                        </ul>
                    </div>

                    <div>
                        <h4 class="mb-3 font-semibold text-white">Quick Links</h4>
                        <ul class="space-y-2">
                            <li>
                                <a
                                    href="#"
                                    class="hover:text-white"
                                    >About Us</a
                                >
                            </li>
                            <li>
                                <a
                                    href="#"
                                    class="hover:text-white"
                                    >Contact</a
                                >
                            </li>
                            <li>
                                <a
                                    href="#"
                                    class="hover:text-white"
                                    >FAQs</a
                                >
                            </li>
                        </ul>
                    </div>

                    <div>
                        <h4 class="mb-3 font-semibold text-white">Policies</h4>
                        <ul class="space-y-2">
                            <li>
                                <a
                                    href="#"
                                    class="hover:text-white"
                                    >Privacy Policy</a
                                >
                            </li>
                            <li>
                                <a
                                    href="#"
                                    class="hover:text-white"
                                    >Terms of Service</a
                                >
                            </li>
                        </ul>
                    </div>
                </div>

                <!-- Right: Contact Links -->
                <div class="text-sm">
                    <h4 class="mb-3 font-semibold text-white">Contact Us</h4>

                    <div class="mt-4 flex items-center gap-4 text-lg">
                        <!-- Facebook -->
                        <a
                            href="https://www.facebook.com/NoynayMedicalClinic"
                            target="_blank"
                            class="hover:text-white"
                        >
                            <Facebook />
                        </a>

                        <!-- Gmail -->
                        <a
                            href="mailto:noynaymedical@gmail.com"
                            class="hover:text-white"
                        >
                            <Mail />
                        </a>

                        <!-- Phone -->
                        <a
                            href="tel:+639288219213"
                            class="hover:text-white"
                        >
                            <Phone />
                        </a>
                    </div>
                </div>
            </div>

            <div class="mt-10 border-t border-zinc-700 pt-6 text-center text-xs text-zinc-500">
                &COPY; {{ new Date().getFullYear() }} Noynay Medical Center Inc. All rights reserved.
            </div>
        </footer>
    </LandingLayout>
</template>
