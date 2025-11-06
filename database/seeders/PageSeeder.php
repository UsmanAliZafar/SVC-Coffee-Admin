<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Page;
use Illuminate\Support\Str;
use Carbon\Carbon;

class PageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $defaultPages = [
            [
                'title' => 'About Us',
                'slug' => 'about-us',
                'excerpt' => 'Learn more about our coffee business and our passion for quality.',
                'content' => '<h2>Welcome to Our Coffee World</h2>
<p>We are passionate about delivering the finest coffee equipment and beans to coffee enthusiasts worldwide. Our journey began with a simple mission: to make exceptional coffee accessible to everyone.</p>

<h3>Our Story</h3>
<p>Founded in [Year], we have been serving coffee lovers with premium machines, carefully selected beans, and reliable spare parts. Our commitment to quality and customer satisfaction has made us a trusted name in the coffee industry.</p>

<h3>Our Mission</h3>
<p>To provide high-quality coffee equipment, premium beans, and exceptional customer service that enhances your coffee experience.</p>

<h3>Why Choose Us?</h3>
<ul>
    <li>Wide selection of premium coffee machines</li>
    <li>Carefully sourced coffee beans from around the world</li>
    <li>Comprehensive spare parts inventory</li>
    <li>Expert customer support</li>
    <li>Fast and reliable shipping</li>
</ul>',
                'meta_title' => 'About Us - Premium Coffee Equipment & Beans',
                'meta_description' => 'Learn about our commitment to providing quality coffee machines, beans, and spare parts. Discover why we are the trusted choice for coffee enthusiasts.',
                'meta_keywords' => 'about us, coffee company, coffee equipment, coffee beans',
                'status' => 'published',
                'visibility' => 'public',
                'template' => 'default',
                'show_in_header' => true,
                'show_in_footer' => true,
                'display_order' => 1,
                'published_at' => Carbon::now(),
            ],
            [
                'title' => 'Contact Us',
                'slug' => 'contact-us',
                'excerpt' => 'Get in touch with our team for any inquiries or support.',
                'content' => '<h2>Get in Touch</h2>
<p>We would love to hear from you! Whether you have a question about our products, need technical support, or want to share feedback, our team is here to help.</p>

<h3>Contact Information</h3>
<p><strong>Email:</strong> info@coffeeshop.com<br>
<strong>Phone:</strong> +1 (555) 123-4567<br>
<strong>Business Hours:</strong> Monday - Friday, 9:00 AM - 6:00 PM</p>

<h3>Customer Support</h3>
<p>For product inquiries, order status, or technical assistance, please contact our dedicated support team.</p>

<h3>Visit Our Store</h3>
<p><strong>Address:</strong><br>
123 Coffee Street<br>
Lahore, Punjab<br>
Pakistan</p>',
                'meta_title' => 'Contact Us - Customer Support',
                'meta_description' => 'Contact our team for product inquiries, technical support, or general questions. We are here to help you with all your coffee equipment needs.',
                'meta_keywords' => 'contact us, customer support, coffee help',
                'status' => 'published',
                'visibility' => 'public',
                'template' => 'default',
                'show_in_header' => true,
                'show_in_footer' => true,
                'display_order' => 2,
                'published_at' => Carbon::now(),
            ],
            [
                'title' => 'Terms and Conditions',
                'slug' => 'terms-and-conditions',
                'excerpt' => 'Terms and conditions for using our website and services.',
                'content' => '<h2>Terms and Conditions</h2>
<p><em>Last updated: ' . Carbon::now()->format('F d, Y') . '</em></p>

<h3>1. Agreement to Terms</h3>
<p>By accessing and using this website, you accept and agree to be bound by the terms and provision of this agreement.</p>

<h3>2. Use License</h3>
<p>Permission is granted to temporarily download one copy of the materials on our website for personal, non-commercial transitory viewing only.</p>

<h3>3. Disclaimer</h3>
<p>The materials on our website are provided on an "as is" basis. We make no warranties, expressed or implied, and hereby disclaim and negate all other warranties.</p>

<h3>4. Limitations</h3>
<p>In no event shall our company or its suppliers be liable for any damages arising out of the use or inability to use the materials on our website.</p>

<h3>5. Accuracy of Materials</h3>
<p>The materials appearing on our website could include technical, typographical, or photographic errors. We do not warrant that any of the materials on our website are accurate, complete, or current.</p>

<h3>6. Links</h3>
<p>We have not reviewed all of the sites linked to our website and are not responsible for the contents of any such linked site.</p>

<h3>7. Modifications</h3>
<p>We may revise these terms of service at any time without notice. By using this website, you are agreeing to be bound by the current version of these terms of service.</p>

<h3>8. Governing Law</h3>
<p>These terms and conditions are governed by and construed in accordance with the laws of Pakistan.</p>',
                'meta_title' => 'Terms and Conditions',
                'meta_description' => 'Read our terms and conditions for using our website and purchasing our coffee equipment and products.',
                'meta_keywords' => 'terms, conditions, legal, agreement',
                'status' => 'published',
                'visibility' => 'public',
                'template' => 'default',
                'show_in_header' => false,
                'show_in_footer' => true,
                'display_order' => 3,
                'published_at' => Carbon::now(),
            ],
            [
                'title' => 'Privacy Policy',
                'slug' => 'privacy-policy',
                'excerpt' => 'How we collect, use, and protect your personal information.',
                'content' => '<h2>Privacy Policy</h2>
<p><em>Last updated: ' . Carbon::now()->format('F d, Y') . '</em></p>

<h3>1. Information We Collect</h3>
<p>We collect information that you provide directly to us when you:</p>
<ul>
    <li>Create an account</li>
    <li>Make a purchase</li>
    <li>Contact customer support</li>
    <li>Subscribe to our newsletter</li>
</ul>

<h3>2. How We Use Your Information</h3>
<p>We use the information we collect to:</p>
<ul>
    <li>Process and fulfill your orders</li>
    <li>Communicate with you about products and services</li>
    <li>Improve our website and customer service</li>
    <li>Send marketing communications (with your consent)</li>
</ul>

<h3>3. Information Sharing</h3>
<p>We do not sell, trade, or rent your personal information to third parties. We may share your information with:</p>
<ul>
    <li>Service providers who assist in our operations</li>
    <li>Law enforcement when required by law</li>
</ul>

<h3>4. Data Security</h3>
<p>We implement appropriate security measures to protect your personal information. However, no method of transmission over the Internet is 100% secure.</p>

<h3>5. Cookies</h3>
<p>We use cookies to enhance your browsing experience. You can choose to disable cookies through your browser settings.</p>

<h3>6. Your Rights</h3>
<p>You have the right to:</p>
<ul>
    <li>Access your personal data</li>
    <li>Correct inaccurate data</li>
    <li>Request deletion of your data</li>
    <li>Opt-out of marketing communications</li>
</ul>

<h3>7. Children\'s Privacy</h3>
<p>Our website is not intended for children under 13 years of age. We do not knowingly collect personal information from children.</p>

<h3>8. Changes to Privacy Policy</h3>
<p>We may update this privacy policy from time to time. We will notify you of any changes by posting the new policy on this page.</p>

<h3>9. Contact Us</h3>
<p>If you have questions about this privacy policy, please contact us at privacy@coffeeshop.com</p>',
                'meta_title' => 'Privacy Policy',
                'meta_description' => 'Learn how we collect, use, and protect your personal information when you use our website.',
                'meta_keywords' => 'privacy policy, data protection, personal information',
                'status' => 'published',
                'visibility' => 'public',
                'template' => 'default',
                'show_in_header' => false,
                'show_in_footer' => true,
                'display_order' => 4,
                'published_at' => Carbon::now(),
            ],
            [
                'title' => 'Shipping & Delivery',
                'slug' => 'shipping-delivery',
                'excerpt' => 'Information about our shipping methods and delivery times.',
                'content' => '<h2>Shipping & Delivery Information</h2>

<h3>Shipping Methods</h3>
<p>We offer several shipping options to meet your needs:</p>
<ul>
    <li><strong>Standard Shipping:</strong> 5-7 business days</li>
    <li><strong>Express Shipping:</strong> 2-3 business days</li>
    <li><strong>Next Day Delivery:</strong> Order before 2 PM for next day delivery</li>
</ul>

<h3>Shipping Costs</h3>
<p>Shipping costs are calculated based on the weight of your order and your delivery location. Free shipping is available on orders over $100.</p>

<h3>International Shipping</h3>
<p>We ship to select international destinations. Delivery times vary by location and customs processing. International customers are responsible for any customs duties or taxes.</p>

<h3>Order Tracking</h3>
<p>Once your order ships, you will receive a tracking number via email. You can track your package status on our website or the courier\'s website.</p>

<h3>Delivery Issues</h3>
<p>If you experience any issues with your delivery, please contact our customer service team immediately.</p>',
                'meta_title' => 'Shipping & Delivery Information',
                'meta_description' => 'Learn about our shipping methods, delivery times, and shipping costs for coffee equipment and products.',
                'meta_keywords' => 'shipping, delivery, shipping costs, tracking',
                'status' => 'published',
                'visibility' => 'public',
                'template' => 'default',
                'show_in_header' => false,
                'show_in_footer' => true,
                'display_order' => 5,
                'published_at' => Carbon::now(),
            ],
            [
                'title' => 'Returns & Refunds',
                'slug' => 'returns-refunds',
                'excerpt' => 'Our return and refund policy for purchased products.',
                'content' => '<h2>Returns & Refunds Policy</h2>

<h3>Return Policy</h3>
<p>We want you to be completely satisfied with your purchase. If you are not satisfied, you may return most items within 30 days of delivery for a full refund.</p>

<h3>Return Conditions</h3>
<p>To be eligible for a return:</p>
<ul>
    <li>Items must be unused and in original packaging</li>
    <li>Coffee beans must be unopened</li>
    <li>Proof of purchase is required</li>
    <li>Items on sale or clearance may not be returnable</li>
</ul>

<h3>How to Return</h3>
<ol>
    <li>Contact our customer service team to initiate a return</li>
    <li>Receive your return authorization and shipping label</li>
    <li>Pack the item securely in original packaging</li>
    <li>Ship the item back using the provided label</li>
</ol>

<h3>Refund Process</h3>
<p>Once we receive your return, we will inspect the item and process your refund within 5-7 business days. Refunds will be credited to your original payment method.</p>

<h3>Exchanges</h3>
<p>If you need to exchange an item for a different size or model, please contact our customer service team.</p>

<h3>Non-Returnable Items</h3>
<p>The following items cannot be returned:</p>
<ul>
    <li>Opened coffee beans packages</li>
    <li>Custom or personalized items</li>
    <li>Items marked as final sale</li>
</ul>

<h3>Defective or Damaged Items</h3>
<p>If you receive a defective or damaged item, please contact us immediately. We will arrange for a replacement or full refund at no cost to you.</p>',
                'meta_title' => 'Returns & Refunds Policy',
                'meta_description' => 'Learn about our return and refund policy for coffee machines, beans, and spare parts.',
                'meta_keywords' => 'returns, refunds, return policy, exchanges',
                'status' => 'published',
                'visibility' => 'public',
                'template' => 'default',
                'show_in_header' => false,
                'show_in_footer' => true,
                'display_order' => 6,
                'published_at' => Carbon::now(),
            ],
        ];

        foreach ($defaultPages as $pageData) {
            Page::create($pageData);
        }

        $this->command->info('Default pages created successfully!');
    }
}
