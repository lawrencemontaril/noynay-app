<script setup lang="ts">
import Button from '@/components/ui/button/Button.vue';
import LandingLayout from '@/layouts/LandingLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { Facebook, Mail, Phone } from 'lucide-vue-next';
import { onMounted, onUnmounted, ref } from 'vue';

const services = {
    consultation: {
        label: 'Consultation',
        children: [
            { label: 'Consultation', value: 'consultation', description: 'General consultation with a physician' },
        ],
    },
    family_planning_service: {
        label: 'Family Planning Services',
        children: [
            {
                label: 'Family Planning Counseling',
                value: 'family_planning_counseling',
                description: 'Guidance on contraceptives and family planning options',
            },
            {
                label: 'Natural Methods (Rhythm), Pills, Depotrust',
                value: 'natural_methods',
                description: 'Includes rhythm method, oral contraceptives, and Depo-Provera injections',
            },
        ],
    },
    integrative_and_wellness: {
        label: 'Integrative and Wellness Healthcare Services',
        children: [
            {
                label: 'Chelation Therapy',
                value: 'chelation_therapy',
                description: 'Removal of heavy metals from the body',
            },
            {
                label: 'Magnetic Resonance Analysis',
                value: 'magnetic_resonance_analysis',
                description: 'Non-invasive body analysis using magnetic resonance',
            },
            {
                label: 'Multifunctional High Potential Therapeutic Services',
                value: 'multifunctional_high_potential_therapeutic_services',
                description: 'Advanced therapeutic modalities for wellness improvement',
            },
            {
                label: 'Weight Loss Management',
                value: 'weight_loss_management',
                description: 'Programs for healthy weight management',
            },
            {
                label: 'Psychosocial and Spiritual Counseling',
                value: 'psychosocial_and_spiritual_counseling',
                description: 'Mental, emotional, and spiritual support services',
            },
        ],
    },
    laboratory_services: {
        label: 'Laboratory Services',
        children: [
            { label: 'Pregnancy Test', value: 'pregnancy_test', description: 'Confirm pregnancy status' },
            { label: 'Papsmear', value: 'papsmear', description: 'Screening test for cervical cancer' },
            { label: 'Complete Blood Count', value: 'cbc', description: 'Analysis of blood components' },
            { label: 'Urinalysis', value: 'urinalysis', description: 'Urine test for infection or other conditions' },
            {
                label: 'Fecalysis',
                value: 'fecalysis',
                description: 'Stool examination for parasites and digestion issues',
            },
        ],
    },
    maternal_and_child_health_services: {
        label: 'Maternal and Child Health Services',
        children: [
            {
                label: 'Pre-Natal and Post-Natal Check Up',
                value: 'pre_natal_and_post_natal',
                description: 'Routine check-ups for mother before and after delivery',
            },
            {
                label: 'Normal Spontaneous Delivery',
                value: 'normal_spontaneous_delivery',
                description: 'Natural childbirth without complications',
            },
            {
                label: 'Immunization - BCG, HEP. B Vaccines, etc.',
                value: 'immunization',
                description: 'Vaccinations for infants and children',
            },
            {
                label: 'Ear Piercing With Hypoallergenic Earrings',
                value: 'ear_pearcing',
                description: 'Safe ear piercing service',
            },
            {
                label: 'Nebulization With and Without Medication',
                value: 'nebulization',
                description: 'Respiratory therapy via nebulizer',
            },
            {
                label: 'Foley Catheter Insertion',
                value: 'foley_catheter_insertion',
                description: 'Urinary catheter insertion',
            },
            {
                label: 'Surgical Wound Dressing',
                value: 'surgical_wound_dressing',
                description: 'Care for post-surgical wounds',
            },
            { label: 'Cord Dressing', value: 'cord_dressing', description: 'Umbilical cord care for newborns' },
            { label: 'Suture Removal', value: 'suture_removal', description: 'Removal of surgical stitches' },
            {
                label: 'Issuance of Birth Certificate; Newborn Screening',
                value: 'issuance_of_bc_newborn_screening',
                description: 'Documentation and health screening for newborns',
            },
        ],
    },
    medical_surgical_services: {
        label: 'Medical/Surgical Services',
        children: [
            {
                label: 'General OPD Consultation',
                value: 'general_opd_consultation',
                description: 'Outpatient department consultation',
            },
            {
                label: 'Medical / OPD / Pre-Employment Consultations',
                value: 'medical_opd_consultation',
                description: 'General health check for employment or routine purposes',
            },
            {
                label: 'Minor Surgical Procedures',
                value: 'minor_surgical_procedures',
                description: 'Small surgical interventions',
            },
            {
                label: 'Issuance of Medical Certificate',
                value: 'issuance_of_medical_certificate',
                description: 'Official medical certification of health',
            },
            {
                label: 'Pedia / Adult Immunization / Vaccination Services',
                value: 'pedia_adult_vaccination_services',
                description: 'Vaccination for children and adults',
            },
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
        .map(([key, el]) => {
            if (!el) return null;
            return { key: key as ServiceKey, el };
        })
        .filter((s): s is { key: ServiceKey; el: HTMLElement } => s !== null);

    observer = new IntersectionObserver(
        () => {
            let closestKey: ServiceKey | null = null;
            let closestDistance = Number.POSITIVE_INFINITY;

            for (const { key, el } of sections) {
                const rect = el.getBoundingClientRect();
                const distance = Math.abs(rect.top);

                if (distance < closestDistance) {
                    closestDistance = distance;
                    closestKey = key;
                }
            }

            activeSection.value = closestKey;
        },
        {
            threshold: [0, 0.25, 0.5, 0.75, 1],
        },
    );

    for (const { el } of sections) {
        observer.observe(el);
    }
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

            <div class="bg-accent py-8 text-center text-lg text-accent-foreground">
                <h1 class="mb-4 text-4xl font-bold">We serve to make you better.</h1>
                <p class="mb-6 text-lg text-accent-foreground">
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
        <section class="flex border-x px-4 py-12">
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
                    <h3 class="sticky top-16 z-10 border-b bg-background px-2 py-3 text-2xl font-bold">
                        {{ service.label }}
                    </h3>

                    <div class="mt-4 grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                        <div
                            v-for="child in service.children"
                            :key="child.value"
                            class="overflow-hidden rounded-lg border shadow-xs"
                        >
                            <img
                                :src="`/images/services/${child.value}.jpg`"
                                :alt="child.label"
                                class="h-48 w-full object-cover"
                            />
                            <div class="p-4">
                                <h4 class="mb-2 text-lg font-semibold">{{ child.label }}</h4>
                                <p class="text-sm text-zinc-600 dark:text-zinc-400">
                                    {{ child.description }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Call to Action -->
        <section class="bg-accent py-16 text-center text-accent-foreground">
            <h2 class="mb-4 text-2xl font-bold">Why Choose Us?</h2>
            <p class="mx-auto mb-6 max-w-2xl">
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
