# Week 12 Activity Submission — Pixmoree

> Working submission record. The activity sheet, Week 11 wireframes, member roster, GitHub repository URL, and scoring matrix were not present in the provided workspace. Complete the marked team fields and attach genuine evidence before submission.

## Lecture Activity — team plan and strategy

### Team roster and assignments

| Team member | GitHub username | Assigned role | Specific implementation task | Reviewer |
|---|---|---|---|---|
| `[Member 1 — add name]` | `[@username]` | Project/Brand Designer | Apply Pixmoree logo, palette, and typography consistently; confirm brand use against Week 11 materials. | UI/UX and Integration Developer |
| `[Member 2 — add name]` | `[@username]` | Admin UI Developer | Implement admin overview and artwork management Blade views using Bootstrap components. | Project/Brand Designer |
| `[Member 3 — add name]` | `[@username]` | Client UI Developer | Implement client marketplace and artwork-detail Blade views using fictional sample data. | Admin UI Developer |
| `[Member 4 — add name]` | `[@username]` | UI/UX and Integration Developer | Integrate named routes and shared layout; check responsive navigation and coordinate testing. | Client UI Developer |

For teams with more than four members, add rows and divide bounded tasks. Role assignments above are a proposed work plan.

**Team GitHub repository:** [https://github.com/edreendorado-max/pixelmoree](https://github.com/edreendorado-max/pixelmoree)  
**Instructor access confirmed:** The repository is public and accessible by link.

### Updated work plan

| Checkpoint | Target | Deliverable / exit check | Owner(s) |
|---|---|---|---|
| 1. Plan and map wireframes | `[Date/time]` | Confirm activity-sheet scoring criteria; map Week 11 client/admin wireframes to routes and assign tasks. | All members |
| 2. Build individual views | `[Date/time]` | Client and admin Blade screens use shared brand tokens and Bootstrap 5 components. | Client UI Developer; Admin UI Developer; Project/Brand Designer |
| 3. Integrate and review | `[Date/time]` | Named routes resolve; shared navigation connects the screens; reviewers log findings and fixes. | UI/UX and Integration Developer; assigned reviewers |
| 4. Test and document | `[Date/time]` | Desktop/mobile checks completed; labeled screenshots and test results collected. | All members |
| 5. Submit | `[Date/time]` | Changes pushed to the team repository; instructor access and scoring-matrix items verified. | All members |

### Combining work

1. Keep the shared route names, layout, brand colors, typography, and asset paths agreed in the planning checkpoint.
2. Each role works on its assigned Blade/CSS area in a separate branch or clearly scoped commit, and requests review from the reviewer listed above.
3. Reviewers check the corresponding Week 11 wireframe, visual consistency, accessibility basics, mobile behavior, and whether demo-only actions are labeled.
4. The UI/UX and Integration Developer merges only reviewed work, resolves route/layout conflicts with the owning developer, and runs the integration checks.
5. All members review the final screenshots and submission evidence; the team pushes the accepted result to the shared repository and verifies instructor access.

## Laboratory Activity — implementation

### Connected screens

| Screen | Route | Purpose |
|---|---|---|
| Client marketplace | `/week12` | Browse fictional digital-art listings. |
| Client artwork detail | `/week12/artworks/{id}` | View listing details and labeled simulated purchase/favorite actions. |
| Admin overview | `/week12/admin` | View fictional summary metrics and navigate to management. |
| Admin artwork management | `/week12/admin/artworks` | Create, approve/unlist, or delete fictional listings stored in MySQL. |

The screens share `resources/views/layouts/week12.blade.php`, use Bootstrap 5.3, and load the Pixmoree logo and existing project artwork assets. Week 12 listing records now persist in the local MySQL/MariaDB `artworks` table; create, approve/unlist, and delete actions are database-backed. Import `database/setup.sql` to create the schema and fictional starter records. There is no authentication or real payment processing; purchases and favorites remain simulated.

### Test results

| Check | Result | Evidence |
|---|---|---|
| PHP syntax check | Passed (`php -l`) | All PHP and Blade-PHP template files linted with XAMPP PHP 8.2.12 on 2026-10-22. |
| MySQL setup | Passed | Imported `database/setup.sql` into local XAMPP MariaDB 10.4.32; confirmed three fictional starter records. |
| Database-backed page rendering | Passed | Local PHP server returned seeded records for the client gallery and detail; admin counters matched the database. |
| Static assets and existing homepage | Passed | Week 12 stylesheet returned HTTP 200 with `text/css`; the existing homepage continued to return HTTP 200. |
| Artwork create/status/delete | Passed | Created a temporary listing over HTTP, changed its status, deleted it, confirmed its detail URL returned 404, and verified only the three starter records remained. |
| CSRF and invalid-input handling | Passed | Missing CSRF token was rejected; invalid price was reported; neither request changed database rows. |
| Laravel route/view rendering | Not run: this workspace contains no Laravel host scaffold (`artisan`, `composer.json`, or `vendor/`). | Validate the Laravel host integration separately if the team copies it into the Week 11 Laravel app. |
| Desktop visual and navigation test | Pending | Add labeled desktop screenshots after visual review in a browser. |
| Mobile visual and navigation test | Pending | Add labeled mobile screenshots after visual review in a browser. |
| Simulated action labels and behaviors | Pending browser check | Verify the demo toast on listing, favorite, admin, and filter actions. |

### Screenshots to attach

Add genuine screenshots to `docs/week12-screenshots/` and link them here:

- **Screenshot 1 — Client marketplace (desktop):** `[Add image/link]`
- **Screenshot 2 — Client artwork detail (mobile):** `[Add image/link]`
- **Screenshot 3 — Admin overview (desktop):** `[Add image/link]`
- **Screenshot 4 — Admin artwork management (mobile):** `[Add image/link]`

### Team reflection

Complete together after integration and testing:

- What went well in planning and collaboration? `[Team response]`
- What integration or responsive-design issue did the team encounter, and how was it resolved? `[Team response]`
- What would the team change in its work plan next time? `[Team response]`
- Which rubric/scoring-matrix criteria were met, and what evidence supports each one? `[Use the activity sheet's actual scoring matrix]`

## Submission blockers to clear

- Replace the roster, usernames, dates, and reviewers with verified team information.
- Provide the Week 12 activity sheet and scoring matrix so the submission can be checked against the exact rubric.
- Compare the implementation with the actual Week 11 wireframes and update any differences.
- Run the Laravel app, complete desktop/mobile testing, attach screenshots and test evidence, and push to the team repository.
