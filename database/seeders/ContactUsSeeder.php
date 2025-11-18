<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ContactUs;
use Illuminate\Support\Facades\DB;

class ContactUsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $contacts = [
            [
                'name' => 'John Doe',
                'email' => 'john.doe@example.com',
                'phone' => '+1234567890',
                'subject' => 'Question about Coffee Machines',
                'message' => 'Hi, I would like to know more about the commercial coffee machines you offer. What are the warranty terms?',
                'status_key_code' => 'CONTACT_PENDING',
                'priority' => 'normal',
                'ip_address' => '192.168.1.1',
                'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
                'created_at' => now()->subDays(2),
                'updated_at' => now()->subDays(2),
            ],
            [
                'name' => 'Jane Smith',
                'email' => 'jane.smith@example.com',
                'phone' => '+1234567891',
                'subject' => 'Spare Parts Inquiry',
                'message' => 'I need a replacement water filter for my Delonghi machine. Do you have part number XYZ-123?',
                'status_key_code' => 'CONTACT_IN_PROGRESS',
                'priority' => 'high',
                'ip_address' => '192.168.1.2',
                'user_agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36',
                'read_at' => now()->subDay(),
                'created_at' => now()->subDays(5),
                'updated_at' => now()->subDay(),
            ],
            [
                'name' => 'Mike Johnson',
                'email' => 'mike.johnson@example.com',
                'phone' => null,
                'subject' => 'Coffee Bean Recommendation',
                'message' => 'Can you recommend a medium roast coffee bean for espresso? I prefer fruity notes.',
                'status_key_code' => 'CONTACT_RESOLVED',
                'priority' => 'normal',
                'ip_address' => '192.168.1.3',
                'user_agent' => 'Mozilla/5.0 (iPhone; CPU iPhone OS 16_0 like Mac OS X) AppleWebKit/605.1.15',
                'read_at' => now()->subDays(6),
                'resolved_at' => now()->subDays(4),
                'admin_notes' => 'Recommended the Ethiopian Yirgacheffe beans. Customer satisfied.',
                'created_at' => now()->subDays(7),
                'updated_at' => now()->subDays(4),
            ],
            [
                'name' => 'Sarah Williams',
                'email' => 'sarah.williams@example.com',
                'phone' => '+1234567892',
                'subject' => 'Bulk Order Query',
                'message' => 'We are a cafe looking to order 10 coffee machines. Can we get a bulk discount?',
                'status_key_code' => 'CONTACT_NEW',
                'priority' => 'high',
                'ip_address' => '192.168.1.4',
                'user_agent' => 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36',
                'created_at' => now()->subHours(5),
                'updated_at' => now()->subHours(5),
            ],
            [
                'name' => 'David Brown',
                'email' => 'david.brown@example.com',
                'phone' => '+1234567893',
                'subject' => 'Machine Repair',
                'message' => 'My machine is not heating properly. Is there a technician available for repairs?',
                'status_key_code' => 'CONTACT_CLOSED',
                'priority' => 'urgent',
                'ip_address' => '192.168.1.5',
                'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:109.0) Gecko/20100101',
                'read_at' => now()->subDays(10),
                'resolved_at' => now()->subDays(8),
                'admin_notes' => 'Customer directed to authorized service center. Issue resolved.',
                'created_at' => now()->subDays(12),
                'updated_at' => now()->subDays(8),
            ],
            [
                'name' => 'Emily Davis',
                'email' => 'emily.davis@example.com',
                'phone' => '+1234567894',
                'subject' => 'Coffee Bean Quality Issue',
                'message' => 'I received a bag of coffee beans that seem stale. Can I get a replacement?',
                'status_key_code' => 'CONTACT_AWAITING_RESPONSE',
                'priority' => 'normal',
                'ip_address' => '192.168.1.6',
                'user_agent' => 'Mozilla/5.0 (iPad; CPU OS 16_0 like Mac OS X) AppleWebKit/605.1.15',
                'read_at' => now()->subHours(10),
                'admin_notes' => 'Requested photos of the product. Waiting for customer response.',
                'created_at' => now()->subDays(3),
                'updated_at' => now()->subHours(10),
            ],
            [
                'name' => 'Robert Martinez',
                'email' => 'robert.martinez@example.com',
                'phone' => '+1234567895',
                'subject' => 'Machine Installation Guide',
                'message' => 'I just purchased a new machine. Do you have an installation guide or video?',
                'status_key_code' => 'CONTACT_FOLLOW_UP',
                'priority' => 'low',
                'ip_address' => '192.168.1.7',
                'user_agent' => 'Mozilla/5.0 (Android 13; Mobile; rv:109.0) Gecko/109.0',
                'read_at' => now()->subHours(8),
                'admin_notes' => 'Sent installation guide. Need to follow up in 2 days to ensure successful installation.',
                'created_at' => now()->subDays(1),
                'updated_at' => now()->subHours(8),
            ],
            [
                'name' => 'Lisa Anderson',
                'email' => 'lisa.anderson@example.com',
                'phone' => '+1234567896',
                'subject' => 'Commercial Machine Warranty Extension',
                'message' => 'We would like to extend the warranty on our commercial coffee machines. What are the options?',
                'status_key_code' => 'CONTACT_ESCALATED',
                'priority' => 'urgent',
                'ip_address' => '192.168.1.8',
                'user_agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 13_0) AppleWebKit/537.36',
                'read_at' => now()->subHours(6),
                'admin_notes' => 'Escalated to sales manager for custom warranty quote.',
                'created_at' => now()->subHours(12),
                'updated_at' => now()->subHours(6),
            ],
            [
                'name' => 'James Wilson',
                'email' => 'james.wilson@example.com',
                'phone' => null,
                'subject' => 'Partnership Inquiry',
                'message' => 'We are interested in becoming a distributor for your coffee machines. Can we discuss partnership opportunities?',
                'status_key_code' => 'CONTACT_ON_HOLD',
                'priority' => 'high',
                'ip_address' => '192.168.1.9',
                'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
                'read_at' => now()->subDays(4),
                'admin_notes' => 'On hold pending approval from business development team.',
                'created_at' => now()->subDays(6),
                'updated_at' => now()->subDays(4),
            ],
            [
                'name' => 'Spam Bot',
                'email' => 'spam@example.com',
                'phone' => null,
                'subject' => 'Buy cheap coffee now!!!',
                'message' => 'Click here to buy cheap coffee beans. Limited time offer! www.suspicious-link.com',
                'status_key_code' => 'CONTACT_SPAM',
                'priority' => 'low',
                'ip_address' => '123.45.67.89',
                'user_agent' => 'curl/7.68.0',
                'read_at' => now()->subDays(8),
                'admin_notes' => 'Marked as spam. IP address blocked.',
                'created_at' => now()->subDays(9),
                'updated_at' => now()->subDays(8),
            ],
            [
                'name' => 'Patricia Thomas',
                'email' => 'patricia.thomas@example.com',
                'phone' => '+1234567897',
                'subject' => 'Coffee Machine Training',
                'message' => 'We need training for our staff on how to use and maintain the commercial coffee machines. Do you offer training sessions?',
                'status_key_code' => 'CONTACT_NEW',
                'priority' => 'normal',
                'ip_address' => '192.168.1.10',
                'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:109.0) Gecko/20100101',
                'created_at' => now()->subHours(2),
                'updated_at' => now()->subHours(2),
            ],
            [
                'name' => 'Michael Garcia',
                'email' => 'michael.garcia@example.com',
                'phone' => '+1234567898',
                'subject' => 'Spare Parts Availability',
                'message' => 'Can you confirm if you have the following spare parts in stock: ABC-456, DEF-789, GHI-012? I need them urgently.',
                'status_key_code' => 'CONTACT_PENDING',
                'priority' => 'urgent',
                'ip_address' => '192.168.1.11',
                'user_agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15',
                'created_at' => now()->subHours(1),
                'updated_at' => now()->subHours(1),
            ],
        ];

        foreach ($contacts as $contact) {
            ContactUs::create($contact);
        }

        $this->command->info('Contact Us seeder completed successfully!');
        $this->command->info('Total contacts created: ' . count($contacts));
        $this->command->info('Status breakdown:');
        $this->command->info('- New: 2');
        $this->command->info('- Pending: 2');
        $this->command->info('- In Progress: 1');
        $this->command->info('- Awaiting Response: 1');
        $this->command->info('- Resolved: 1');
        $this->command->info('- Closed: 1');
        $this->command->info('- Spam: 1');
        $this->command->info('- Follow Up: 1');
        $this->command->info('- Escalated: 1');
        $this->command->info('- On Hold: 1');
    }
}
