<?php

namespace Database\Seeders;

use App\Models\Appointment;
use App\Models\Category;
use App\Models\Document;
use App\Models\Feedback;
use App\Models\Message;
use App\Models\Notification;
use App\Models\Office;
use App\Models\Payment;
use App\Models\QrCode;
use App\Models\RequestStatusHistory;
use App\Models\Role;
use App\Models\Service;
use App\Models\ServiceRequest;
use App\Models\User;
use App\Services\PdfGenerationService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DemoDataSeeder extends Seeder
{
    private const DEMO_TAG = 'demo-seed-v2';

    private const PASSWORD = 'password123';

    private const TARGET_DEMO_REQUESTS = 160;

    private const MIN_REQUESTS_PER_OFFICE = 14;

    /** @var array<string, Office> */
    private array $offices = [];

    /** @var array<string, Category> */
    private array $categories = [];

    /** @var array<int, Service> */
    private array $services = [];

    /** @var array<int, User> */
    private array $staff = [];

    /** @var array<int, User> */
    private array $citizens = [];

    private string $demoIdPath = 'ids/seed-demo-verified.png';

    private string $demoCitizenUploadPdfPath = 'documents/demo-citizen-upload.pdf';

    public function run(): void
    {
        $this->ensureDemoIdFile();
        $this->ensureDemoCitizenUploadPdf();
        $this->repairMislabeledCitizenDocuments();
        $this->backfillGeneratedPdfsForDemoRequests();

        $adminRole = Role::query()->firstOrCreate(['slug' => 'admin'], ['name' => 'Admin']);
        $staffRole = Role::query()->firstOrCreate(['slug' => 'office_staff'], ['name' => 'Office Staff']);
        $citizenRole = Role::query()->firstOrCreate(['slug' => 'citizen'], ['name' => 'Citizen']);

        $this->seedCoreUsers($adminRole, $staffRole, $citizenRole);
        $this->seedOffices();
        $this->seedStaff($staffRole);
        $this->seedCategories();
        $this->seedServices();
        $this->seedCitizens($citizenRole);

        $existingDemoRequests = ServiceRequest::query()
            ->where('notes', 'like', 'demo-seed%')
            ->count();

        if ($existingDemoRequests < self::TARGET_DEMO_REQUESTS) {
            $this->seedServiceRequestsAndRelated(self::TARGET_DEMO_REQUESTS - $existingDemoRequests);
        }

        $this->ensureRichDemoDataPerOffice();
        $this->command?->info('Lebanon demo data seeded successfully.');
    }

    private function ensureDemoIdFile(): void
    {
        if (Storage::disk('public')->exists($this->demoIdPath)) {
            return;
        }

        Storage::disk('public')->makeDirectory('ids');
        Storage::disk('public')->put(
            $this->demoIdPath,
            base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8BQDwAEhQGAhKmMIQAAAABJRU5ErkJggg==')
        );
    }

    private function seedCoreUsers(Role $adminRole, Role $staffRole, Role $citizenRole): void
    {
        User::query()->updateOrCreate(['email' => 'admin@example.com'], [
            'name' => 'Platform Admin',
            'password' => Hash::make(self::PASSWORD),
            'role_id' => $adminRole->id,
            'email_verified_at' => now(),
            'two_factor_verified_at' => now(),
        ]);

        User::query()->updateOrCreate(['email' => 'citizen@example.com'], [
            'name' => 'Nour El Hassan',
            'password' => Hash::make(self::PASSWORD),
            'role_id' => $citizenRole->id,
            'email_verified_at' => now(),
            'two_factor_verified_at' => now(),
            'id_document_path' => $this->demoIdPath,
            'phone' => '+961 3 123 456',
            'date_of_birth' => '1995-03-12',
        ]);
    }

    private function seedOffices(): void
    {
        $arCatalog = require database_path('data/localized_catalog_ar.php');
        $officeAr = $arCatalog['offices'];

        $definitions = [
            'beirut' => [
                'name' => 'Beirut Municipal Council — Sanayeh',
                'municipality' => 'Beirut',
                'address' => 'Sanayeh, Emir Bachir Street, Beirut',
                'contact_number' => '+961 1 350 000',
                'contact_email' => 'info@beirut.gov.lb',
                'latitude' => 33.8938,
                'longitude' => 35.5018,
            ],
            'tripoli' => [
                'name' => 'Tripoli Municipality — Maarad',
                'municipality' => 'Tripoli',
                'address' => 'Maarad Square, Tripoli',
                'contact_number' => '+961 6 442 110',
                'contact_email' => 'contact@tripoli.gov.lb',
                'latitude' => 34.4367,
                'longitude' => 35.8497,
            ],
            'sidon' => [
                'name' => 'Saida Municipality — Old City',
                'municipality' => 'Saida',
                'address' => 'Riad Al Solh Street, Saida',
                'contact_number' => '+961 7 722 001',
                'contact_email' => 'municipality@saida.gov.lb',
                'latitude' => 33.5631,
                'longitude' => 35.3689,
            ],
            'zahle' => [
                'name' => 'Zahle Municipality — Maalaka',
                'municipality' => 'Zahle',
                'address' => 'Maalaka Boulevard, Zahle',
                'contact_number' => '+961 8 802 200',
                'contact_email' => 'info@zahle.gov.lb',
                'latitude' => 33.8463,
                'longitude' => 35.9018,
            ],
            'jounieh' => [
                'name' => 'Keserwan–Jbeil Governorate Services — Jounieh',
                'municipality' => 'Jounieh',
                'address' => 'Kaslik Highway, Jounieh',
                'contact_number' => '+961 9 918 400',
                'contact_email' => 'services@keserwan.gov.lb',
                'latitude' => 33.9808,
                'longitude' => 35.6171,
            ],
            'baabda' => [
                'name' => 'Baabda District Administration',
                'municipality' => 'Baabda',
                'address' => 'Baabda Government Palace Road',
                'contact_number' => '+961 5 920 150',
                'contact_email' => 'admin@baabda.gov.lb',
                'latitude' => 33.8338,
                'longitude' => 35.5442,
            ],
            'tyre' => [
                'name' => 'Sour (Tyre) Municipality',
                'municipality' => 'Sour',
                'address' => 'Al Bourj Street, Tyre',
                'contact_number' => '+961 7 740 300',
                'contact_email' => 'info@sourmunicipality.gov.lb',
                'latitude' => 33.2734,
                'longitude' => 35.1939,
            ],
            'nabatieh' => [
                'name' => 'Nabatieh Municipality',
                'municipality' => 'Nabatieh',
                'address' => 'Nabatieh Main Road',
                'contact_number' => '+961 7 765 100',
                'contact_email' => 'contact@nabatieh.gov.lb',
                'latitude' => 33.3783,
                'longitude' => 35.4838,
            ],
        ];

        foreach ($definitions as $key => $data) {
            $this->offices[$key] = Office::query()->updateOrCreate(
                ['name' => $data['name']],
                array_merge($data, $officeAr[$key] ?? [], [
                    'working_hours' => [
                        'days' => ['Mon', 'Tue', 'Wed', 'Thu', 'Fri'],
                        'hours' => '8:00–15:00',
                        'note' => 'Closed on Lebanese public holidays',
                    ],
                ])
            );
        }
    }

    private function seedStaff(Role $staffRole): void
    {
        $staffAccounts = [
            ['email' => 'staff@example.com', 'name' => 'Rana Khoury', 'office' => 'beirut'],
            ['email' => 'staff.tripoli@example.com', 'name' => 'Ahmad Merhebi', 'office' => 'tripoli'],
            ['email' => 'staff.saida@example.com', 'name' => 'Layla Awada', 'office' => 'sidon'],
            ['email' => 'staff.zahle@example.com', 'name' => 'Elias Farah', 'office' => 'zahle'],
            ['email' => 'staff.jounieh@example.com', 'name' => 'Maya Gemayel', 'office' => 'jounieh'],
            ['email' => 'staff.baabda@example.com', 'name' => 'Hassan Hamdan', 'office' => 'baabda'],
            ['email' => 'staff.tyre@example.com', 'name' => 'Sara Jaafar', 'office' => 'tyre'],
            ['email' => 'staff.nabatieh@example.com', 'name' => 'Karim Fadel', 'office' => 'nabatieh'],
            ['email' => 'staff.beirut2@example.com', 'name' => 'Georges Nasr', 'office' => 'beirut'],
        ];

        foreach ($staffAccounts as $account) {
            $user = User::query()->updateOrCreate(['email' => $account['email']], [
                'name' => $account['name'],
                'password' => Hash::make(self::PASSWORD),
                'role_id' => $staffRole->id,
                'office_id' => $this->offices[$account['office']]->id,
                'email_verified_at' => now(),
                'two_factor_verified_at' => now(),
                'phone' => '+961 '.fake()->numerify('## ### ###'),
            ]);
            $this->staff[] = $user;
        }
    }

    private function seedCategories(): void
    {
        $arCatalog = require database_path('data/localized_catalog_ar.php');
        $categoryAr = $arCatalog['categories'];

        $definitions = [
            'civil' => ['name' => 'Civil Registration & Extracts', 'description' => 'Birth, family, and residence records.'],
            'licenses' => ['name' => 'Municipal Licenses & Permits', 'description' => 'Shop signage, outdoor seating, events.'],
            'planning' => ['name' => 'Urban Planning & Construction', 'description' => 'Building permits and zoning inquiries.'],
            'health' => ['name' => 'Public Health & Sanitation', 'description' => 'Inspections and health certificates.'],
            'business' => ['name' => 'Business & Commercial Registry', 'description' => 'Municipal business registration support.'],
            'property' => ['name' => 'Property & Land Records', 'description' => 'Municipal property attestations.'],
            'social' => ['name' => 'Social Services', 'description' => 'Local aid and community programs.'],
            'traffic' => ['name' => 'Traffic & Parking', 'description' => 'Parking permits and traffic-related services.'],
        ];

        foreach ($definitions as $key => $data) {
            $this->categories[$key] = Category::query()->updateOrCreate(
                ['name' => $data['name']],
                array_merge(
                    ['description' => $data['description']],
                    $categoryAr[$key] ?? []
                )
            );
        }
    }

    private function seedServices(): void
    {
        $arCatalog = require database_path('data/localized_catalog_ar.php');
        $serviceNamesAr = $arCatalog['service_names'];
        $descriptionArTemplate = $arCatalog['service_description_ar'];
        $requiredDocumentsAr = $arCatalog['required_documents_ar'];

        $catalog = [
            ['office' => 'beirut', 'category' => 'civil', 'name' => 'Extract of Residence (إخراج قيد)', 'price' => 15.00, 'minutes' => 20],
            ['office' => 'beirut', 'category' => 'licenses', 'name' => 'Shop Signage Permit', 'price' => 120.00, 'minutes' => 45],
            ['office' => 'beirut', 'category' => 'planning', 'name' => 'Renovation Permit — Residential', 'price' => 250.00, 'minutes' => 120],
            ['office' => 'beirut', 'category' => 'traffic', 'name' => 'Annual Parking Sticker — Zone A', 'price' => 85.00, 'minutes' => 15],
            ['office' => 'beirut', 'category' => 'health', 'name' => 'Restaurant Sanitary Certificate', 'price' => 95.00, 'minutes' => 60],
            ['office' => 'tripoli', 'category' => 'civil', 'name' => 'Family Record Copy (نسخة قيد)', 'price' => 12.00, 'minutes' => 25],
            ['office' => 'tripoli', 'category' => 'licenses', 'name' => 'Weekly Souk Vendor Permit', 'price' => 35.00, 'minutes' => 30],
            ['office' => 'tripoli', 'category' => 'business', 'name' => 'New Shop Opening Registration', 'price' => 75.00, 'minutes' => 40],
            ['office' => 'sidon', 'category' => 'property', 'name' => 'Property Boundary Attestation', 'price' => 110.00, 'minutes' => 90],
            ['office' => 'sidon', 'category' => 'planning', 'name' => 'Coastal Zone Construction Inquiry', 'price' => 180.00, 'minutes' => 75],
            ['office' => 'sidon', 'category' => 'health', 'name' => 'Food Handler Health Card', 'price' => 40.00, 'minutes' => 30],
            ['office' => 'zahle', 'category' => 'licenses', 'name' => 'Outdoor Café Terrace License', 'price' => 90.00, 'minutes' => 35],
            ['office' => 'zahle', 'category' => 'social', 'name' => 'Winter Heating Aid Application', 'price' => 0.00, 'minutes' => 25],
            ['office' => 'zahle', 'category' => 'civil', 'name' => 'Proof of Life Certificate', 'price' => 8.00, 'minutes' => 15],
            ['office' => 'jounieh', 'category' => 'traffic', 'name' => 'Tourist Bus Parking Authorization', 'price' => 150.00, 'minutes' => 50],
            ['office' => 'jounieh', 'category' => 'licenses', 'name' => 'Beach Club Seasonal License', 'price' => 320.00, 'minutes' => 90],
            ['office' => 'baabda', 'category' => 'planning', 'name' => 'Hillside Construction Permit', 'price' => 275.00, 'minutes' => 100],
            ['office' => 'baabda', 'category' => 'property', 'name' => 'Land Subdivision Request', 'price' => 200.00, 'minutes' => 120],
            ['office' => 'tyre', 'category' => 'licenses', 'name' => 'Fishing Port Activity Permit', 'price' => 65.00, 'minutes' => 40],
            ['office' => 'tyre', 'category' => 'social', 'name' => 'Fisher Families Support Program', 'price' => 0.00, 'minutes' => 30],
            ['office' => 'nabatieh', 'category' => 'civil', 'name' => 'Municipal Good Conduct Certificate', 'price' => 10.00, 'minutes' => 20],
            ['office' => 'nabatieh', 'category' => 'business', 'name' => 'Agricultural Cooperative Registration', 'price' => 55.00, 'minutes' => 45],
            ['office' => 'nabatieh', 'category' => 'health', 'name' => 'Public Market Hygiene Inspection', 'price' => 50.00, 'minutes' => 55],
        ];

        foreach ($catalog as $item) {
            $municipality = $this->offices[$item['office']]->municipality ?? '';
            $service = Service::query()->updateOrCreate(
                [
                    'office_id' => $this->offices[$item['office']]->id,
                    'name' => $item['name'],
                ],
                [
                    'category_id' => $this->categories[$item['category']]->id,
                    'name_ar' => $serviceNamesAr[$item['name']] ?? null,
                    'description' => 'Municipal e-service for residents of '.$municipality.'.',
                    'description_ar' => str_replace(
                        ':municipality',
                        $this->offices[$item['office']]->municipality_ar ?? $municipality,
                        $descriptionArTemplate
                    ),
                    'price' => $item['price'],
                    'estimated_duration_minutes' => $item['minutes'],
                    'required_documents' => ['National ID or passport', 'Proof of address', 'Supporting forms if applicable'],
                    'required_documents_ar' => $requiredDocumentsAr,
                    'is_active' => true,
                ]
            );
            $this->services[] = $service;
        }
    }

    private function seedCitizens(Role $citizenRole): void
    {
        $names = [
            ['Nour El Hassan', 'citizen@example.com'],
            ['Karim Haddad', 'citizen.karim@example.com'],
            ['Mira Abou Chakra', 'citizen.mira@example.com'],
            ['Omar Saad', 'citizen.omar@example.com'],
            ['Rita Mansour', 'citizen.rita@example.com'],
            ['Fadi Chamoun', 'citizen.fadi@example.com'],
            ['Yara Daher', 'citizen.yara@example.com'],
            ['Tarek Saliba', 'citizen.tarek@example.com'],
            ['Hiba Kanaan', 'citizen.hiba@example.com'],
            ['Walid Issa', 'citizen.walid@example.com'],
            ['Lina Sfeir', 'citizen.lina@example.com'],
            ['Bilal Nasser', 'citizen.bilal@example.com'],
            ['Christine Gemayel', 'citizen.christine@example.com'],
            ['Ziad Rahbani', 'citizen.ziad@example.com'],
            ['Maya Khoury', 'citizen.maya@example.com'],
            ['Samir Tabet', 'citizen.samir@example.com'],
            ['Dina Mouawad', 'citizen.dina@example.com'],
            ['Patrick Hayek', 'citizen.patrick@example.com'],
            ['Reem Chehab', 'citizen.reem@example.com'],
            ['Anthony Boulos', 'citizen.anthony@example.com'],
        ];

        foreach ($names as $index => [$name, $email]) {
            $user = User::query()->updateOrCreate(['email' => $email], [
                'name' => $name,
                'password' => Hash::make(self::PASSWORD),
                'role_id' => $citizenRole->id,
                'email_verified_at' => now(),
                'two_factor_verified_at' => now(),
                'id_document_path' => $this->demoIdPath,
                'phone' => '+961 3'.fake()->numerify(' ## ####'),
                'date_of_birth' => now()->subYears(22 + ($index % 35))->format('Y-m-d'),
            ]);
            $this->citizens[] = $user;
        }
    }

    private function seedServiceRequestsAndRelated(int $count = self::TARGET_DEMO_REQUESTS): void
    {
        $statuses = ['pending', 'in_review', 'missing_documents', 'approved', 'rejected', 'completed'];
        $statusWeights = [
            'pending' => 12,
            'in_review' => 14,
            'missing_documents' => 8,
            'approved' => 10,
            'rejected' => 6,
            'completed' => 24,
        ];

        $weightedStatuses = [];
        foreach ($statusWeights as $status => $weight) {
            $weightedStatuses = array_merge($weightedStatuses, array_fill(0, $weight, $status));
        }

        $staffByOffice = collect($this->staff)->groupBy('office_id');
        $referenceSeeds = ServiceRequest::query()->count() + 1;

        for ($i = 0; $i < $count; $i++) {
            $service = $this->services[array_rand($this->services)];
            $citizen = $this->citizens[array_rand($this->citizens)];
            $status = $weightedStatuses[array_rand($weightedStatuses)];
            $submittedAt = now()->subDays(random_int(1, 180))->subHours(random_int(0, 12));
            $reference = (string) Str::uuid();

            $request = ServiceRequest::query()->create([
                'reference_number' => $reference,
                'citizen_id' => $citizen->id,
                'service_id' => $service->id,
                'office_id' => $service->office_id,
                'status' => $status,
                'notes' => self::DEMO_TAG,
                'submitted_at' => $submittedAt,
                'created_at' => $submittedAt,
                'updated_at' => $submittedAt->copy()->addDays(random_int(0, 14)),
            ]);

            $officeStaff = $staffByOffice->get($service->office_id)?->first()
                ?? $this->staff[0];

            $this->seedStatusHistory($request, $status, $officeStaff, $submittedAt);

            if (in_array($status, ['approved', 'completed'], true)) {
                $this->seedPayment($request, $citizen, $service, $submittedAt);
                $this->seedGeneratedPdfs($request);
            }

            if (in_array($status, ['in_review', 'approved', 'completed'], true) && random_int(0, 100) < 65) {
                $this->seedAppointment($request, $citizen, $officeStaff, $submittedAt);
            }

            if ($status === 'completed' && random_int(0, 100) < 85) {
                $this->seedFeedback($request, $citizen, $service);
            }

            if (in_array($status, ['in_review', 'missing_documents', 'approved', 'completed'], true) && random_int(0, 100) < 55) {
                $this->seedMessageThread($request, $citizen, $officeStaff);
            }

            if (random_int(0, 100) < 40) {
                $this->seedCitizenDocument($request, $citizen);
            }

            if (in_array($status, ['approved', 'completed'], true)) {
                QrCode::query()->firstOrCreate(
                    ['service_request_id' => $request->id],
                    [
                        'token' => 'DEMO-'.strtoupper(Str::random(10)).'-'.$referenceSeeds,
                        'expires_at' => now()->addMonths(6),
                    ]
                );
            }

            if (random_int(0, 100) < 45) {
                $this->seedNotification($citizen, $service->name, $status, $reference, $submittedAt);
            }

            if (random_int(0, 100) < 30) {
                $this->seedNotification($officeStaff, $service->name, $status, $reference, $submittedAt);
            }

            $referenceSeeds++;
        }
    }

    private function ensureRichDemoDataPerOffice(): void
    {
        $staffByOffice = collect($this->staff)->groupBy('office_id');
        $statusRotation = ['pending', 'in_review', 'missing_documents', 'approved', 'rejected', 'completed'];

        foreach ($this->offices as $office) {
            $officeStaff = $staffByOffice->get($office->id)?->first() ?? $this->staff[0];
            $officeServices = Service::query()->where('office_id', $office->id)->get();

            if ($officeServices->isEmpty()) {
                continue;
            }

            $requestCount = ServiceRequest::query()->where('office_id', $office->id)->count();
            $needed = max(0, self::MIN_REQUESTS_PER_OFFICE - $requestCount);

            for ($i = 0; $i < $needed; $i++) {
                $status = $statusRotation[$i % count($statusRotation)];
                $this->createDemoRequestForOffice($office, $officeServices->random(), $status, $officeStaff);
            }

            $this->topUpOfficeFeedback($office, $officeStaff, 4);
            $this->topUpOfficeMessages($office, $officeStaff, 8);
            $this->topUpOfficeAppointments($office, $officeStaff, 5);
            $this->topUpOfficeNotifications($office, $officeStaff, 6);
        }
    }

    private function createDemoRequestForOffice(
        Office $office,
        Service $service,
        string $status,
        User $officeStaff,
    ): ServiceRequest {
        $citizen = $this->citizens[array_rand($this->citizens)];
        $submittedAt = now()->subDays(random_int(3, 120))->subHours(random_int(0, 10));
        $reference = (string) Str::uuid();

        $request = ServiceRequest::query()->create([
            'reference_number' => $reference,
            'citizen_id' => $citizen->id,
            'service_id' => $service->id,
            'office_id' => $office->id,
            'status' => $status,
            'notes' => self::DEMO_TAG,
            'submitted_at' => $submittedAt,
            'created_at' => $submittedAt,
            'updated_at' => $submittedAt->copy()->addDays(random_int(1, 10)),
        ]);

        $this->seedStatusHistory($request, $status, $officeStaff, $submittedAt);
        $this->seedCitizenDocument($request, $citizen);

        if (in_array($status, ['approved', 'completed'], true)) {
            $this->seedPayment($request, $citizen, $service, $submittedAt);
            $this->seedGeneratedPdfs($request);
            QrCode::query()->firstOrCreate(
                ['service_request_id' => $request->id],
                [
                    'token' => 'DEMO-'.strtoupper(Str::random(12)),
                    'expires_at' => now()->addMonths(6),
                ]
            );
        }

        if (in_array($status, ['in_review', 'approved', 'completed'], true)) {
            $this->seedAppointment($request, $citizen, $officeStaff, $submittedAt);
        }

        if ($status === 'completed') {
            $this->seedFeedback($request, $citizen, $service);
        }

        if (in_array($status, ['in_review', 'missing_documents'], true)) {
            $this->seedMessageThread($request, $citizen, $officeStaff);
        }

        $this->seedNotification($citizen, $service->name, $status, $reference, $submittedAt);

        return $request;
    }

    private function topUpOfficeFeedback(Office $office, User $officeStaff, int $minimum): void
    {
        $current = Feedback::query()->where('office_id', $office->id)->count();

        for ($i = $current; $i < $minimum; $i++) {
            $request = ServiceRequest::query()
                ->where('office_id', $office->id)
                ->where('status', 'completed')
                ->inRandomOrder()
                ->first();

            if (! $request) {
                $service = Service::query()->where('office_id', $office->id)->first();
                if (! $service) {
                    break;
                }
                $request = $this->createDemoRequestForOffice($office, $service, 'completed', $officeStaff);
            }

            $citizen = User::query()->find($request->citizen_id);
            $service = Service::query()->find($request->service_id);

            if ($citizen && $service) {
                $this->seedFeedback($request, $citizen, $service);
            }
        }
    }

    private function topUpOfficeMessages(Office $office, User $officeStaff, int $minimum): void
    {
        $current = Message::query()
            ->whereHas('serviceRequest', fn ($q) => $q->where('office_id', $office->id))
            ->count();

        while ($current < $minimum) {
            $request = ServiceRequest::query()
                ->where('office_id', $office->id)
                ->inRandomOrder()
                ->first();

            if (! $request) {
                break;
            }

            $citizen = User::query()->find($request->citizen_id);
            if ($citizen) {
                $this->seedMessageThread($request, $citizen, $officeStaff);
                $current += 2;
            } else {
                break;
            }
        }
    }

    private function topUpOfficeAppointments(Office $office, User $officeStaff, int $minimum): void
    {
        $current = Appointment::query()->where('office_id', $office->id)->count();

        while ($current < $minimum) {
            $request = ServiceRequest::query()
                ->where('office_id', $office->id)
                ->inRandomOrder()
                ->first();

            $citizen = $request
                ? User::query()->find($request->citizen_id)
                : $this->citizens[array_rand($this->citizens)];

            if (! $citizen) {
                break;
            }

            $start = now()->addDays(random_int(2, 30))->setTime(random_int(8, 15), 0);

            Appointment::query()->create([
                'service_request_id' => $request?->id,
                'office_id' => $office->id,
                'citizen_id' => $citizen->id,
                'staff_id' => $officeStaff->id,
                'starts_at' => $start,
                'ends_at' => $start->copy()->addMinutes(30),
                'status' => random_int(0, 1) ? 'scheduled' : 'completed',
                'notes' => 'Demo appointment — municipal front desk visit.',
            ]);

            $current++;
        }
    }

    private function topUpOfficeNotifications(Office $office, User $officeStaff, int $minimum): void
    {
        $staffCount = Notification::query()->where('user_id', $officeStaff->id)->count();
        $citizenIds = ServiceRequest::query()
            ->where('office_id', $office->id)
            ->pluck('citizen_id')
            ->unique();

        for ($i = $staffCount; $i < $minimum; $i++) {
            $this->seedNotification(
                $officeStaff,
                'Office activity — '.$office->municipality,
                'in_review',
                'DEMO-'.$office->id.'-'.$i,
                now()->subDays(random_int(1, 14))
            );
        }

        foreach ($citizenIds->take(5) as $citizenId) {
            $citizen = User::query()->find($citizenId);
            if (! $citizen) {
                continue;
            }

            if (Notification::query()->where('user_id', $citizen->id)->count() >= 3) {
                continue;
            }

            $this->seedNotification(
                $citizen,
                'Municipal update — '.$office->municipality,
                'completed',
                'DEMO-N-'.$office->id.'-'.$citizenId,
                now()->subDays(random_int(1, 20))
            );
        }
    }

    private function seedStatusHistory(
        ServiceRequest $request,
        string $finalStatus,
        User $staff,
        Carbon $submittedAt,
    ): void {
        $chain = ['pending', 'in_review'];
        if (in_array($finalStatus, ['missing_documents', 'approved', 'rejected', 'completed'], true)) {
            $chain[] = $finalStatus === 'missing_documents' ? 'missing_documents' : 'in_review';
        }
        if (in_array($finalStatus, ['approved', 'completed'], true)) {
            $chain[] = 'approved';
        }
        if ($finalStatus === 'completed') {
            $chain[] = 'completed';
        }
        if ($finalStatus === 'rejected') {
            $chain = ['pending', 'in_review', 'rejected'];
        }

        $chain = array_values(array_unique($chain));
        $previous = null;
        $at = $submittedAt->copy();

        foreach ($chain as $toStatus) {
            if ($toStatus === $finalStatus && $toStatus !== end($chain)) {
                continue;
            }

            RequestStatusHistory::query()->create([
                'service_request_id' => $request->id,
                'changed_by' => $staff->id,
                'from_status' => $previous,
                'to_status' => $toStatus,
                'comment' => 'Demo status change via municipal portal.',
                'changed_at' => $at,
                'created_at' => $at,
            ]);

            $previous = $toStatus;
            $at = $at->copy()->addHours(random_int(4, 72));
        }

        if ($previous !== $finalStatus) {
            RequestStatusHistory::query()->create([
                'service_request_id' => $request->id,
                'changed_by' => $staff->id,
                'from_status' => $previous,
                'to_status' => $finalStatus,
                'comment' => 'Final demo status update.',
                'changed_at' => $at,
                'created_at' => $at,
            ]);
        }
    }

    private function seedPayment(ServiceRequest $request, User $citizen, Service $service, Carbon $submittedAt): void
    {
        $paid = in_array($request->status, ['approved', 'completed'], true);
        Payment::query()->create([
            'service_request_id' => $request->id,
            'user_id' => $citizen->id,
            'method' => random_int(0, 1) ? 'card' : 'crypto',
            'amount' => $service->price,
            'currency' => 'USD',
            'status' => $paid ? 'paid' : 'pending',
            'gateway_reference' => 'LB-DEMO-'.strtoupper(Str::random(8)),
            'paid_at' => $paid ? $submittedAt->copy()->addDay() : null,
            'created_at' => $submittedAt,
        ]);
    }

    private function seedAppointment(ServiceRequest $request, User $citizen, User $staff, Carbon $submittedAt): void
    {
        $start = $submittedAt->copy()->addDays(random_int(3, 20))->setTime(9, 0);
        Appointment::query()->create([
            'service_request_id' => $request->id,
            'office_id' => $request->office_id,
            'citizen_id' => $citizen->id,
            'staff_id' => $staff->id,
            'starts_at' => $start,
            'ends_at' => $start->copy()->addMinutes(30),
            'status' => $request->status === 'completed' ? 'completed' : 'scheduled',
            'notes' => 'In-person visit at municipality reception.',
        ]);
    }

    private function seedFeedback(ServiceRequest $request, User $citizen, Service $service): void
    {
        $comments = [
            'Staff were helpful and the process was clear.',
            'Wait time was reasonable; would appreciate SMS updates.',
            'Very smooth experience at the '.$service->office->municipality.' office.',
            'Needed one extra document but the officer explained clearly.',
        ];

        Feedback::query()->create([
            'service_request_id' => $request->id,
            'citizen_id' => $citizen->id,
            'office_id' => $request->office_id,
            'rating' => random_int(3, 5),
            'comment' => $comments[array_rand($comments)],
            'public_reply' => random_int(0, 1) ? 'Thank you for your feedback — Municipality of '.$service->office->municipality : null,
        ]);
    }

    private function seedMessageThread(ServiceRequest $request, User $citizen, User $staff): void
    {
        $pairs = [
            [$staff, $citizen, 'Please upload the missing document listed in your request checklist.'],
            [$citizen, $staff, 'I have attached the requested file. Please confirm receipt.'],
            [$staff, $citizen, 'Thank you — we are reviewing your submission and will update the status shortly.'],
        ];

        $count = random_int(2, 3);

        for ($i = 0; $i < $count; $i++) {
            [$sender, $recipient, $body] = $pairs[$i];

            Message::query()->create([
                'service_request_id' => $request->id,
                'sender_id' => $sender->id,
                'recipient_id' => $recipient->id,
                'message' => $body,
                'read_at' => random_int(0, 1) ? now() : null,
                'created_at' => now()->subDays(random_int(0, 10)),
            ]);
        }
    }

    private function ensureDemoCitizenUploadPdf(): void
    {
        if (Storage::disk('public')->exists($this->demoCitizenUploadPdfPath)) {
            return;
        }

        Storage::disk('public')->makeDirectory('documents');

        $pdf = Pdf::loadView('pdfs.citizen-upload-demo', [
            'reference' => 'DEMO-SEED',
        ]);

        Storage::disk('public')->put($this->demoCitizenUploadPdfPath, $pdf->output());
    }

    private function repairMislabeledCitizenDocuments(): void
    {
        Document::query()
            ->where('type', 'required')
            ->where('file_path', $this->demoIdPath)
            ->where('mime_type', 'application/pdf')
            ->update([
                'file_path' => $this->demoCitizenUploadPdfPath,
                'size' => Storage::disk('public')->size($this->demoCitizenUploadPdfPath),
            ]);
    }

    private function backfillGeneratedPdfsForDemoRequests(): void
    {
        ServiceRequest::query()
            ->where('notes', 'like', 'demo-seed%')
            ->whereIn('status', ['approved', 'completed'])
            ->whereDoesntHave('documents', fn ($query) => $query->where('type', 'generated_pdf'))
            ->with(['service', 'office', 'citizen'])
            ->each(fn (ServiceRequest $request) => $this->seedGeneratedPdfs($request));
    }

    private function seedGeneratedPdfs(ServiceRequest $request): void
    {
        if ($request->documents()->where('type', 'generated_pdf')->exists()) {
            return;
        }

        $request->loadMissing(['service', 'office', 'citizen']);
        $pdfService = app(PdfGenerationService::class);

        if ($request->status === 'approved') {
            $path = $pdfService->generateApprovalCertificate($request);
        } else {
            $path = $pdfService->generateResponseLetter($request);
        }

        $receiptPath = $pdfService->generateReceipt($request);

        foreach ([$path, $receiptPath] as $generatedPath) {
            $request->documents()->create([
                'uploaded_by' => $request->citizen_id,
                'type' => 'generated_pdf',
                'file_path' => $generatedPath,
                'original_name' => basename($generatedPath),
                'mime_type' => 'application/pdf',
                'size' => Storage::disk('public')->size($generatedPath),
            ]);
        }
    }

    private function seedCitizenDocument(ServiceRequest $request, User $citizen): void
    {
        if (Document::query()->where('service_request_id', $request->id)->where('type', 'required')->exists()) {
            return;
        }

        Document::query()->create([
            'service_request_id' => $request->id,
            'uploaded_by' => $citizen->id,
            'type' => 'required',
            'file_path' => $this->demoCitizenUploadPdfPath,
            'original_name' => 'citizen-upload-'.$request->reference_number.'.pdf',
            'mime_type' => 'application/pdf',
            'size' => Storage::disk('public')->size($this->demoCitizenUploadPdfPath),
        ]);
    }

    private function seedNotification(
        User $user,
        string $serviceName,
        string $status,
        string $reference,
        Carbon $at,
    ): void {
        Notification::query()->create([
            'user_id' => $user->id,
            'title' => 'Request update — '.$serviceName,
            'body' => 'Your request '.$reference.' is now: '.str_replace('_', ' ', $status).'.',
            'data' => ['reference' => $reference, 'status' => $status],
            'read_at' => random_int(0, 1) ? now() : null,
            'created_at' => $at->copy()->addHours(random_int(2, 48)),
        ]);
    }
}
