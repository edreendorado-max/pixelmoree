<?php

namespace App\Support;

use App\Models\SitePage;

class SitePageContent
{
    public static function definitions(): array
    {
        $teamMemberFields = [];
        for ($index = 0; $index < 4; $index++) {
            $number = $index + 1;
            $teamMemberFields[] = self::field("team.members.{$index}.name", "Developer {$number} name", 'text', 120);
            $teamMemberFields[] = self::field("team.members.{$index}.role", "Developer {$number} role", 'text', 120);
            $teamMemberFields[] = self::field("team.members.{$index}.bio", "Developer {$number} introduction", 'textarea', 500);
            $teamMemberFields[] = self::field("team.members.{$index}.section", "Developer {$number} section", 'text', 120);
            $teamMemberFields[] = self::field("team.members.{$index}.quote", "Developer {$number} quote", 'textarea', 300);
            $teamMemberFields[] = self::field("team.members.{$index}.image", "Developer {$number} photo", 'image');
            foreach (['instagram' => 'Instagram', 'facebook' => 'Facebook', 'github' => 'GitHub', 'linkedin' => 'LinkedIn'] as $platform => $label) {
                $teamMemberFields[] = self::field("team.members.{$index}.socials.{$platform}", "Developer {$number} {$label} URL", 'url', 255);
            }
        }

        return [
            'home' => [
                'label' => 'Homepage',
                'groups' => [
                    [
                        'label' => 'Page details',
                        'fields' => [
                            self::field('meta_title', 'Browser title', 'text', 160),
                            self::field('meta_description', 'Search description', 'textarea', 300),
                        ],
                    ],
                    [
                        'label' => 'Hero section',
                        'fields' => [
                            self::field('hero_heading', 'Heading', 'text', 100),
                            self::field('hero_tagline', 'Tagline', 'textarea', 300),
                            self::field('hero_benefits.0', 'First highlight', 'text', 200),
                            self::field('hero_benefits.1', 'Second highlight', 'textarea', 300),
                            self::field('hero_cta', 'Button label', 'text', 60),
                            self::field('hero_background', 'Hero background image', 'image'),
                        ],
                    ],
                    [
                        'label' => 'Trending, creators, and gallery',
                        'fields' => [
                            self::field('trending.heading', 'Trending heading', 'text', 120),
                            self::field('trending.description', 'Trending description', 'text', 200),
                            self::field('trending.view_all', 'Trending link label', 'text', 60),
                            self::field('trending.empty_heading', 'Empty trending heading', 'text', 120),
                            self::field('trending.empty_body', 'Empty trending message', 'textarea', 300),
                            self::field('creators.heading', 'Creators heading', 'text', 120),
                            self::field('creators.description', 'Creators description', 'text', 200),
                            self::field('creators.role_label', 'Creator card label', 'text', 60),
                            self::field('creators.empty_message', 'Empty creators message', 'text', 200),
                            self::field('explore.heading', 'Gallery heading', 'text', 120),
                            self::field('explore.description', 'Gallery description', 'text', 200),
                            self::field('explore.filters.0', 'First gallery filter', 'text', 40),
                            self::field('explore.filters.1', 'Second gallery filter', 'text', 40),
                            self::field('explore.filters.2', 'Third gallery filter', 'text', 40),
                            self::field('explore.filters.3', 'Fourth gallery filter', 'text', 40),
                            self::field('explore.sort_label', 'Sort label', 'text', 40),
                            self::field('explore.load_more', 'Load more button', 'text', 60),
                            self::field('explore.empty_message', 'Empty gallery message', 'text', 200),
                        ],
                    ],
                    [
                        'label' => 'Closing section and footer',
                        'fields' => [
                            self::field('closing_heading', 'Closing heading', 'text', 120),
                            self::field('closing_body', 'Closing description', 'textarea', 500),
                            self::field('closing_cta', 'Button label', 'text', 60),
                            self::field('footer_tagline', 'Footer tagline', 'text', 200),
                            self::field('footer_note', 'Footer note', 'text', 200),
                            self::field('footer_links.0', 'First footer link', 'text', 40),
                            self::field('footer_links.1', 'Second footer link', 'text', 40),
                            self::field('footer_links.2', 'Third footer link', 'text', 40),
                            self::field('footer_links.3', 'Fourth footer link', 'text', 40),
                        ],
                    ],
                ],
            ],
            'marketplace' => [
                'label' => 'Marketplace',
                'groups' => [
                    [
                        'label' => 'Marketplace introduction',
                        'fields' => [
                            self::field('meta_title', 'Browser title', 'text', 160),
                            self::field('meta_description', 'Search description', 'textarea', 300),
                            self::field('hero.eyebrow', 'Hero label', 'text', 100),
                            self::field('hero.heading_before', 'Heading before emphasis', 'text', 100),
                            self::field('hero.heading_emphasis', 'Emphasized word', 'text', 60),
                            self::field('hero.heading_after', 'Heading after emphasis', 'text', 100),
                            self::field('hero.description', 'Introduction', 'textarea', 500),
                            self::field('hero.cta', 'Button label', 'text', 80),
                            self::field('hero.note', 'Classroom note', 'text', 300),
                            self::field('hero.image', 'Marketplace hero image', 'image'),
                            self::field('hero.image_alt', 'Marketplace hero image description', 'text', 255),
                            self::field('artwork.eyebrow', 'Artwork section label', 'text', 100),
                            self::field('artwork.heading', 'Artwork section heading', 'text', 120),
                            self::field('artwork.count_label', 'Artwork count label', 'text', 100),
                            self::field('artwork.filters_label', 'Filters button', 'text', 60),
                            self::field('artwork.empty_heading', 'Empty gallery heading', 'text', 120),
                            self::field('artwork.empty_body', 'Empty gallery message', 'textarea', 300),
                        ],
                    ],
                    [
                        'label' => 'Marketplace footer',
                        'fields' => [
                            self::field('footer.tagline', 'Footer tagline', 'text', 200),
                            self::field('footer.disclaimer', 'Footer note', 'text', 250),
                        ],
                    ],
                ],
            ],
            'about' => [
                'label' => 'About page',
                'groups' => [
                    [
                        'label' => 'Page details and hero',
                        'fields' => [
                            self::field('meta_title', 'Browser title', 'text', 160),
                            self::field('meta_description', 'Search description', 'textarea', 300),
                            self::field('hero.eyebrow', 'Hero label', 'text', 100),
                            self::field('hero.heading', 'Hero heading', 'text', 120),
                            self::field('hero.description', 'Hero description', 'textarea', 500),
                            self::field('hero.link', 'Gallery link label', 'text', 100),
                            self::field('hero.image', 'Hero artwork', 'image'),
                            self::field('hero.image_alt', 'Hero artwork description', 'text', 255),
                            self::field('hero.caption', 'Hero artwork caption', 'text', 120),
                        ],
                    ],
                    [
                        'label' => 'About and mission sections',
                        'fields' => [
                            self::field('stories.0.eyebrow', 'About section label', 'text', 100),
                            self::field('stories.0.heading', 'About section heading', 'text', 160),
                            self::field('stories.0.body', 'About section text', 'textarea', 1000),
                            self::field('stories.0.image', 'About section image', 'image'),
                            self::field('stories.0.image_alt', 'Background image description', 'text', 255),
                            self::field('stories.1.eyebrow', 'Mission section label', 'text', 100),
                            self::field('stories.1.heading', 'Mission heading', 'text', 160),
                            self::field('stories.1.body', 'Mission text', 'textarea', 1000),
                            self::field('stories.1.image', 'Mission artwork', 'image'),
                            self::field('stories.1.image_alt', 'Mission artwork description', 'text', 255),
                            self::field('stories.2.eyebrow', 'Gallery section label', 'text', 100),
                            self::field('stories.2.heading', 'Gallery heading', 'text', 160),
                            self::field('stories.2.body', 'Gallery text', 'textarea', 1000),
                            self::field('stories.2.image', 'Gallery artwork', 'image'),
                            self::field('stories.2.image_alt', 'Gallery artwork description', 'text', 255),
                            self::field('stories.2.link_label', 'Gallery link label', 'text', 80),
                        ],
                    ],
                    [
                        'label' => 'Project overview',
                        'fields' => [
                            self::field('project.eyebrow', 'Section label', 'text', 100),
                            self::field('project.heading', 'Heading', 'text', 160),
                            self::field('project.body', 'Project description', 'textarea', 1000),
                            self::field('project.image', 'Project artwork', 'image'),
                            self::field('project.image_alt', 'Project artwork description', 'text', 255),
                            self::field('project.caption', 'Image caption', 'text', 120),
                            self::field('project.started_at', 'Project start date', 'date', 10),
                            self::field('project.started_label', 'Project start label', 'text', 80),
                            self::field('project.note', 'Project note', 'text', 200),
                            self::field('project.tools_label', 'Tools section label', 'text', 60),
                            self::field('overview.eyebrow', 'Overview label', 'text', 100),
                            self::field('overview.heading', 'Overview heading', 'text', 160),
                            self::field('overview.items.0.heading', 'Purpose heading', 'text', 120),
                            self::field('overview.items.0.body', 'Purpose text', 'textarea', 500),
                            self::field('overview.items.1.heading', 'Features heading', 'text', 120),
                            self::field('overview.items.1.body', 'Features text', 'textarea', 500),
                            self::field('overview.items.2.heading', 'Learning goals heading', 'text', 120),
                            self::field('overview.items.2.body', 'Learning goals text', 'textarea', 500),
                            self::field('overview.items.3.heading', 'Tools heading', 'text', 120),
                            self::field('overview.items.3.body', 'Tools text', 'textarea', 500),
                            self::field('overview.items.4.heading', 'Scope heading', 'text', 120),
                            self::field('overview.items.4.body', 'Scope text', 'textarea', 500),
                        ],
                    ],
                    [
                        'label' => 'People behind Pixmoree',
                        'fields' => array_merge([
                            self::field('team.eyebrow', 'Section label', 'text', 100),
                            self::field('team.heading', 'Heading', 'text', 160),
                            self::field('team.intro', 'Introduction', 'textarea', 500),
                            self::field('team.privacy_note', 'Team note', 'text', 300),
                        ], $teamMemberFields),
                    ],
                    [
                        'label' => 'Contact and footer',
                        'fields' => [
                            self::field('contact.eyebrow', 'Section label', 'text', 100),
                            self::field('contact.heading', 'Heading', 'text', 160),
                            self::field('contact.body', 'Contact text', 'textarea', 500),
                            self::field('contact.link_label', 'Contact link', 'text', 80),
                            self::field('footer_tagline', 'Footer tagline', 'text', 200),
                            self::field('footer_note', 'Footer note', 'text', 200),
                            self::field('footer_links.0', 'First footer link', 'text', 40),
                            self::field('footer_links.1', 'Second footer link', 'text', 40),
                            self::field('footer_links.2', 'Third footer link', 'text', 40),
                        ],
                    ],
                ],
            ],
            'login' => [
                'label' => 'Log in',
                'groups' => [[
                    'label' => 'Log-in page copy',
                    'fields' => [
                        self::field('meta_title', 'Browser title', 'text', 160),
                        self::field('meta_description', 'Search description', 'textarea', 300),
                        self::field('eyebrow', 'Intro label', 'text', 100),
                        self::field('heading', 'Heading', 'text', 120),
                        self::field('intro', 'Introduction', 'textarea', 300),
                        self::field('email_label', 'Email label', 'text', 60),
                        self::field('email_placeholder', 'Email placeholder', 'text', 120),
                        self::field('password_label', 'Password label', 'text', 60),
                        self::field('password_placeholder', 'Password placeholder', 'text', 120),
                        self::field('forgot_password', 'Password reset link', 'text', 60),
                        self::field('submit_label', 'Submit button', 'text', 60),
                        self::field('divider_label', 'Social sign-in divider', 'text', 100),
                        self::field('google_label', 'Google button', 'text', 80),
                        self::field('facebook_label', 'Facebook button', 'text', 80),
                        self::field('switch_prompt', 'Sign-up prompt', 'text', 100),
                        self::field('switch_link', 'Sign-up link', 'text', 60),
                        self::field('legal_intro', 'Terms introduction', 'text', 160),
                        self::field('terms_label', 'Terms link', 'text', 60),
                        self::field('privacy_connector', 'Terms/privacy connector', 'text', 30),
                        self::field('privacy_label', 'Privacy link', 'text', 60),
                        self::field('back_label', 'Back link', 'text', 60),
                    ],
                ]],
            ],
            'register' => [
                'label' => 'Create account',
                'groups' => [[
                    'label' => 'Registration page copy',
                    'fields' => [
                        self::field('meta_title', 'Browser title', 'text', 160),
                        self::field('meta_description', 'Search description', 'textarea', 300),
                        self::field('eyebrow', 'Intro label', 'text', 100),
                        self::field('heading', 'Heading', 'text', 120),
                        self::field('intro', 'Introduction', 'textarea', 300),
                        self::field('divider_label', 'Email sign-up divider', 'text', 100),
                        self::field('google_label', 'Google button', 'text', 80),
                        self::field('facebook_label', 'Facebook button', 'text', 80),
                        self::field('name_label', 'Name label', 'text', 60),
                        self::field('name_placeholder', 'Name placeholder', 'text', 120),
                        self::field('email_label', 'Email label', 'text', 60),
                        self::field('email_placeholder', 'Email placeholder', 'text', 120),
                        self::field('password_label', 'Password label', 'text', 60),
                        self::field('password_placeholder', 'Password placeholder', 'text', 120),
                        self::field('confirm_label', 'Password confirmation label', 'text', 80),
                        self::field('confirm_placeholder', 'Password confirmation placeholder', 'text', 120),
                        self::field('terms_intro', 'Terms checkbox introduction', 'text', 100),
                        self::field('terms_label', 'Terms link', 'text', 60),
                        self::field('privacy_connector', 'Terms/privacy connector', 'text', 30),
                        self::field('privacy_label', 'Privacy link', 'text', 60),
                        self::field('submit_label', 'Submit button', 'text', 60),
                        self::field('switch_prompt', 'Log-in prompt', 'text', 100),
                        self::field('switch_link', 'Log-in link', 'text', 60),
                        self::field('back_label', 'Back link', 'text', 60),
                    ],
                ]],
            ],
        ];
    }

    public static function defaults(): array
    {
        return [
            'home' => [
                'meta_title' => 'Pixmoree — Discover Digital Collectibles',
                'meta_description' => 'Explore digital art, anime-inspired collectibles, and creators on Pixmoree.',
                'hero_heading' => 'Welcome!',
                'hero_tagline' => 'Enter a new dimension of digital creativity.',
                'hero_benefits' => [
                    'Discover the extraordinary in digital art.',
                    'Explore unique creations, collect digital treasures, and find art that speaks to you.',
                ],
                'hero_cta' => 'Explore',
                'hero_background' => 'assets/guest-hero-background.png',
                'trending' => [
                    'heading' => 'Trending',
                    'description' => "Trending NFT's today",
                    'view_all' => 'View all',
                    'empty_heading' => 'No artwork yet',
                    'empty_body' => 'New creator listings will appear here.',
                ],
                'creators' => [
                    'heading' => 'Trending Creators',
                    'description' => 'Creators with listed work on Pixmoree',
                    'role_label' => 'Creator',
                    'empty_message' => 'Creator profiles will appear when artwork is listed.',
                ],
                'explore' => [
                    'heading' => 'Explore',
                    'description' => 'Find your next favorite piece',
                    'filters' => ['All items', 'Newest', 'Price', 'Favorites'],
                    'sort_label' => 'Sort by',
                    'load_more' => 'Load more items',
                    'empty_message' => 'No artwork has been listed yet.',
                ],
                'closing_heading' => 'Creativity has a new home.',
                'closing_body' => 'Explore digital art, discover emerging creators, and find your next favorite piece on Pixmoree.',
                'closing_cta' => 'Join the community',
                'footer_tagline' => 'Discover a universe of digital creativity.',
                'footer_note' => 'Made for creators and collectors.',
                'footer_links' => ['Explore', 'Creators', 'Community', 'Support'],
            ],
            'marketplace' => [
                'meta_title' => 'Pixmoree Marketplace — Explore Artwork',
                'meta_description' => 'Browse digital art listings from Pixmoree creators.',
                'hero' => [
                    'eyebrow' => 'PIXMOREE MARKETPLACE',
                    'heading_before' => 'Find your next',
                    'heading_emphasis' => 'favorite',
                    'heading_after' => 'world.',
                    'description' => 'Browse imaginative digital art from independent creators.',
                    'cta' => 'Explore the gallery',
                    'note' => 'Artwork listings are stored in MySQL. Purchases and blockchain transactions are not enabled.',
                    'image' => 'assets/guest-hero-background.png',
                    'image_alt' => 'Colorful Pixmoree digital art collage',
                ],
                'artwork' => [
                    'eyebrow' => 'DISCOVER',
                    'heading' => 'Featured artwork',
                    'count_label' => 'listings loaded from the database.',
                    'filters_label' => 'Filters (demo)',
                    'empty_heading' => 'No artwork has been added yet',
                    'empty_body' => 'Add a supplied artwork listing from the admin artwork screen.',
                ],
                'footer' => [
                    'tagline' => 'Pixmoree Marketplace',
                    'disclaimer' => 'Sample artwork listings. No payments are processed.',
                ],
            ],
            'about' => [
                'meta_title' => 'About Pixmoree — Digital Collectibles Marketplace',
                'meta_description' => 'Learn about Pixmoree, a student-created digital collectibles marketplace concept for discovering and showcasing digital art.',
                'hero' => [
                    'eyebrow' => 'OUR STORY',
                    'heading' => 'A little more',
                    'description' => 'A colorful corner of the internet for discovering digital art, meeting creators, and finding something that feels uniquely yours.',
                    'link' => 'Explore the gallery',
                    'image' => 'assets/nfts/ethereal-life.png',
                    'image_alt' => 'Ethereal Life digital artwork',
                    'caption' => 'Discover something new',
                ],
                'stories' => [
                    [
                        'eyebrow' => '01 / ABOUT PIXMOREE',
                        'heading' => 'Digital art, waiting to be discovered.',
                        'body' => 'Pixmoree is a digital collectibles marketplace concept focused on discovering and showcasing digital artwork. It brings imaginative pieces together in one place, making it easy to browse art and see the people behind it.',
                        'image' => 'assets/guest-hero-background.png',
                        'image_alt' => 'A colorful digital universe, representing the Pixmoree marketplace',
                    ],
                    [
                        'eyebrow' => '02 / OUR MISSION',
                        'heading' => 'Make room for every kind of creator.',
                        'body' => 'Our goal is to offer a simple, visually engaging place where people can explore digital artwork and discover different artists. We want the experience to feel welcoming, inspiring, and fun to come back to.',
                        'image' => 'assets/nfts/rather-lie.png',
                        'image_alt' => 'Artwork supplied for the Pixmoree gallery',
                    ],
                    [
                        'eyebrow' => '03 / WHAT YOU CAN DO',
                        'heading' => 'Explore at your own pace.',
                        'body' => 'Browse collections, open artwork to see its details, check out what is trending, and save pieces you love to your favorites. Pixmoree is designed to make finding your next inspiration feel effortless.',
                        'image' => 'assets/nfts/sneaker-study.jpg',
                        'image_alt' => 'Artwork supplied for the Pixmoree gallery',
                        'link_label' => 'Browse the gallery',
                    ],
                ],
                'project' => [
                    'eyebrow' => '04 / ABOUT THE PROJECT',
                    'heading' => 'Built as a learning experience.',
                    'body' => 'Pixmoree is an educational project developed by students to practice web design, frontend development, and marketplace functionality. The site is a creative concept, made to bring those skills together in one interactive experience.',
                    'image' => 'assets/nfts/neon-character.png',
                    'image_alt' => 'Student-created artwork featured on Pixmoree',
                    'caption' => 'Made while learning',
                    'started_at' => '2026-10-15',
                    'started_label' => 'Project started',
                    'note' => 'Curiosity, creativity, and code — all in one project.',
                    'tools_label' => 'Apps used',
                ],
                'overview' => [
                    'eyebrow' => 'STUDENT PROJECT OVERVIEW',
                    'heading' => 'Purpose, scope, and learning goals.',
                    'items' => [
                        ['heading' => 'Project purpose', 'body' => 'Explore how a digital-art marketplace can help people discover artwork and creators through a clear, accessible interface.'],
                        ['heading' => 'Core features', 'body' => 'Artwork discovery, search and filters, favorites, creator listings, user collections, NFT detail views, and simulated checkout.'],
                        ['heading' => 'Learning goals', 'body' => 'Practice responsive UI design, reusable page patterns, browser storage, form validation, accessibility, and responsible handling of creative work.'],
                        ['heading' => 'Tools and stack', 'body' => 'PHP/Laravel-compatible views, HTML, CSS, JavaScript, and Bootstrap, with Photoshop and After Effects for visual assets.'],
                        ['heading' => 'Scope and limitations', 'body' => 'This is a student prototype. Accounts and artwork listings are stored in MySQL; payments and blockchain transactions are simulated and are not enabled.'],
                    ],
                ],
                'team' => [
                    'eyebrow' => '05 / MEET THE DEVELOPERS',
                    'heading' => 'The people behind Pixmoree.',
                    'intro' => 'Meet the developers who designed and built the Pixmoree website.',
                    'privacy_note' => 'The development team behind the Pixmoree website.',
                    'members' => [
                        ['name' => 'Edreen Dorado', 'role' => 'Developer', 'bio' => '', 'section' => '', 'quote' => '', 'image' => 'assets/creators/edreen.jpg', 'socials' => ['instagram' => '', 'facebook' => '', 'github' => '', 'linkedin' => '']],
                        ['name' => 'Noah', 'role' => 'Developer', 'bio' => '', 'section' => '', 'quote' => '', 'image' => 'assets/creators/noah.jpg', 'socials' => ['instagram' => '', 'facebook' => '', 'github' => '', 'linkedin' => '']],
                        ['name' => 'Kenneth', 'role' => 'Developer', 'bio' => '', 'section' => '', 'quote' => '', 'image' => 'assets/creators/kenneth.png', 'socials' => ['instagram' => '', 'facebook' => '', 'github' => '', 'linkedin' => '']],
                        ['name' => 'Jet', 'role' => 'Developer', 'bio' => '', 'section' => '', 'quote' => '', 'image' => 'assets/creators/jet.jpg', 'socials' => ['instagram' => '', 'facebook' => '', 'github' => '', 'linkedin' => '']],
                    ],
                ],
                'contact' => [
                    'eyebrow' => '06 / CONTACT & PROJECT INFO',
                    'heading' => 'Have a question about the project?',
                    'body' => 'Pixmoree is a student-built digital-art marketplace concept for showcasing original creative work.',
                    'link_label' => 'Back to home',
                ],
                'footer_tagline' => 'Discover a universe of digital creativity.',
                'footer_note' => 'Made for creators and collectors.',
                'footer_links' => ['Explore', 'Creators', 'About'],
            ],
            'login' => [
                'meta_title' => 'Log in — Pixmoree',
                'meta_description' => 'Log in to your Pixmoree account and continue exploring digital art.',
                'eyebrow' => 'YOUR NEXT DIMENSION AWAITS',
                'heading' => 'Welcome back',
                'intro' => 'Log in to pick up where your creativity left off.',
                'email_label' => 'Email address',
                'email_placeholder' => 'you@example.com',
                'password_label' => 'Password',
                'password_placeholder' => 'Enter your password',
                'forgot_password' => 'Forgot password?',
                'submit_label' => 'Log in',
                'divider_label' => 'or continue with',
                'google_label' => 'Continue with Google',
                'facebook_label' => 'Continue with Facebook',
                'switch_prompt' => 'New to Pixmoree?',
                'switch_link' => 'Create an account',
                'legal_intro' => 'By continuing, you agree to Pixmoree’s',
                'terms_label' => 'Terms',
                'privacy_connector' => 'and',
                'privacy_label' => 'Privacy Policy',
                'back_label' => 'Back to Pixmoree',
            ],
            'register' => [
                'meta_title' => 'Create an account — Pixmoree',
                'meta_description' => 'Create a Pixmoree account to save your favorite digital artwork and discover creators.',
                'eyebrow' => 'FIND YOUR PLACE IN THE PIXELVERSE',
                'heading' => 'Create your account',
                'intro' => 'Join the community and save the art that speaks to you.',
                'divider_label' => 'or sign up with email',
                'google_label' => 'Continue with Google',
                'facebook_label' => 'Continue with Facebook',
                'name_label' => 'Display name',
                'name_placeholder' => 'How should we call you?',
                'email_label' => 'Email address',
                'email_placeholder' => 'you@example.com',
                'password_label' => 'Password',
                'password_placeholder' => 'At least 8 characters',
                'confirm_label' => 'Confirm password',
                'confirm_placeholder' => 'Enter your password again',
                'terms_intro' => 'I agree to the',
                'terms_label' => 'Terms',
                'privacy_connector' => 'and',
                'privacy_label' => 'Privacy Policy',
                'submit_label' => 'Create account',
                'switch_prompt' => 'Already have an account?',
                'switch_link' => 'Log in',
                'back_label' => 'Back to Pixmoree',
            ],
        ];
    }

    public static function get(string $slug): array
    {
        $defaults = self::defaults();
        abort_unless(array_key_exists($slug, $defaults), 404);

        $stored = SitePage::query()->where('slug', $slug)->first()?->content;

        return array_replace_recursive($defaults[$slug], is_array($stored) ? $stored : []);
    }

    private static function field(string $key, string $label, string $type, int $max = 0): array
    {
        return [
            'key' => $key,
            'label' => $label,
            'type' => $type,
            'max' => $max,
        ];
    }
}
