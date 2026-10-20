<?php

namespace Database\Seeders;

use App\Enum\StatusType;
use App\Models\Dashboard\JobCategory;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class JobCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $jobCategories = [
            ['name' => 'Accounting/Finance', 'status' => StatusType::ACTIVE],
            ['name' => 'Agro (Plant/Animal/Fisheries)', 'status' => StatusType::ACTIVE],
            ['name' => 'Bank/Non-Bank Fin. Institution', 'status' => StatusType::ACTIVE],
            ['name' => 'Beauty Care/ Health & Fitness', 'status' => StatusType::ACTIVE],
            ['name' => 'Commercial', 'status' => StatusType::ACTIVE],
            ['name' => 'Company Secretary/Regulatory affairs', 'status' => StatusType::ACTIVE],
            ['name' => 'Data Entry/Operator/BPO', 'status' => StatusType::ACTIVE],
            ['name' => 'Design/Creative', 'status' => StatusType::ACTIVE],
            ['name' => 'Driving/Motor Technician', 'status' => StatusType::ACTIVE],
            ['name' => 'Education/Training', 'status' => StatusType::ACTIVE],
            ['name' => 'Electrician/ Construction/ Repair', 'status' => StatusType::ACTIVE],
            ['name' => 'Engineer/Architect', 'status' => StatusType::ACTIVE],
            ['name' => 'Garments/ Textile', 'status' => StatusType::ACTIVE],
            ['name' => 'General Management/Admin', 'status' => StatusType::ACTIVE],
            ['name' => 'Hospitality/ Travel/ Tourism', 'status' => StatusType::ACTIVE],
            ['name' => 'HR/Org. Development', 'status' => StatusType::ACTIVE],
            ['name' => 'IT/Telecommunication', 'status' => StatusType::ACTIVE],
            ['name' => 'Law/ Legal', 'status' => StatusType::ACTIVE],
            ['name' => 'Marketing/Sales', 'status' => StatusType::ACTIVE],
            ['name' => 'Media/Advertisement/Event Mgt.', 'status' => StatusType::ACTIVE],
            ['name' => 'Medical/Pharma', 'status' => StatusType::ACTIVE],
            ['name' => 'NGO/Development', 'status' => StatusType::ACTIVE],
            ['name' => 'Production/Operation', 'status' => StatusType::ACTIVE],
            ['name' => 'Receptionist/ PS', 'status' => StatusType::ACTIVE],
            ['name' => 'Research/Consultancy', 'status' => StatusType::ACTIVE],
            ['name' => 'Security/Support Service', 'status' => StatusType::ACTIVE],
            ['name' => 'Supply Chain/Procurement', 'status' => StatusType::ACTIVE],
            ['name' => 'Others', 'status' => StatusType::ACTIVE],
        ];

        foreach( $jobCategories  as $category){
            JobCategory::create($category);
        }
    }
}
