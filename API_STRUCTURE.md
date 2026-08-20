# API Structure Reference

Snapshot of `kinokonstructor-api` for scoping/estimation work (e.g. Кинопросвет). Generated from the actual codebase on 2026-07-31 — not from CLAUDE.md, which is stale (says Laravel 9; composer.json shows **Laravel 12**).

## Stack

- PHP 8.3, Laravel 12, served via Octane + RoadRunner
- Auth: Sanctum (API tokens) + Fortify (registration/password/email-verify flows)
- Media: Spatie Media Library v11 (`app/Service/Media/`)
- Query filtering: Spatie Query Builder v7
- External content: scraper services pulling from a Strapi CMS

## Request flow

`routes/api.php` → Controller → Service class (`app/Service/`) → Model → API Resource (`app/Http/Resources/`, Brief vs Full variants).

## Core patterns to reuse for new features

| Pattern | Where | Use for |
|---|---|---|
| `Moderatable` trait — Draft → Pending → Accepted/Rejected, `onlyAccepted`/`visibleTo` scopes | `app/Traits/Moderation/` | Кинопедагог approval, project/material moderation |
| `Favoritable` trait (polymorphic) | `app/Traits/Favoritable.php` | "избранное" on any new entity (videos, projects) |
| `Archivable` trait (polymorphic) | `app/Traits/Archivable.php` | soft-archive on new entities |
| `Requestable` / `Contactable` traits | `app/Traits/` | contact/request forms |
| Roles as JSON array on `User` (`roles` column), `hasRole()`/`isAdmin()`/`isSpecialist()` | `app/Models/User.php` | adding `кинопедагог` role — append value, no new table needed |
| `moderator` middleware | route group in `routes/api.php` | gating admin-only endpoints |
| Media collections via `InteractsWithMedia` | any model, e.g. `Video.php` | file uploads (video, image, docs) to own server |
| Brief vs Full API Resources | `app/Http/Resources/` | list vs detail payload shaping |

No `spatie/laravel-permission` — roles are flat strings, not a relational RBAC table. No tagging/taxonomy model anywhere. No websocket/chat infra.

## Models (`app/Models/`)

AppModel (base), BranchMember, BranchNews, Comment, Contact, Contest, ContestApplication, ContestContact, **Course**, Department, Event, EventApplication, Favorite, Feedback, **Lesson**, Location, Occupation, Profile, ProfileCustomProjects, ProfileEducation, ProfileExperience, **Project**, ProjectMember, Region, RegionalBranch, Report, Request, Resource, Resume, **Semester**, Teacher, User, Vacancy, **Video**.

Bold = most relevant precedent for Кинопросвет (Course/Lesson/Semester → education-section pattern; Project → multi-field form-with-moderation pattern; Video → target entity to extend).

### Video (`app/Models/Video.php`)
Fillable: `title, description, category (array), details (array), video_link, external_link, owner_id`. Media collections: `video_file`, `image_file`. Has `owner()` (belongsTo User), `comments()` (morphMany), `Favoritable`. **No moderation, no tags, no age rating, no license field today.**

### User (`app/Models/User.php`)
Fillable: `name, username, email, allow_newsletter, password, roles`. `roles` is a plain JSON array cast, no pivot table. `profile()` hasOne `Profile`.

## Controllers (`app/Http/Controllers/`)

Admin/UserAdminController, BranchMemberController, BranchNewsController, ContestApplicationController, ContestController, Controller (base), **CourseController**, DashboardController, DictionaryController, EventApplicationController, EventController, FeedbackController, InviteController, **LessonController**, LocationController, MemberController, NotificationController, PasswordController, ProfileController, ProjectController, RegionalBranchController, ReportController, ResourceController, ResumeController, TokenController, UserController, VacancyController, VerifyEmailController, **VideoController**.

Admin surface is thin: only `UserAdminController` (user list, verify/unverify, set_role, profile approve/reject). No generic entity-admin scaffold — every new admin-manageable entity (e.g. Кинопросвет projects, подборки) needs its own controller/routes hand-built following this pattern.

## Services (`app/Service/`)

ContestService, **CourseService**, EventService, Events/CultureScraper, Media/{MediaFileNamer, MediaPathGenerator, MediaService}, News/{KinoNewsScraper, MovieStartScraper}, **ProjectService**, RegionalBranchService, ResourceService, Resources/CinemapScraper, ResumeService, Shared/ContactHandlerService, StrapiService, TelegramService, VacancyService, **VideoService**.

Notably **no dedicated service for Video moderation, tags, or collections** — `VideoService` is currently minimal (upload + basic CRUD only).

## Routes (`routes/api.php`, 217 lines)

Public (no auth): dictionaries, `videos` (show/index only), contests (show/index), regions, regional-branches (show/index), resources (show/index), events (show/index), courses (show/index), lessons (show).

Auth (`sanctum`) + `verified`: profile CRUD, locations, contests (full CRUD + apply/favorite/archive), **projects** (full CRUD + moderate/approve/reject/invite/locations/export/favorite/archive — richest example of a moderated multi-actor entity), reports, feedback.

`moderator` middleware group: user admin (verify/unverify/set_role), profile approve/reject. **This is the exact pattern a "moderate кинопедагог application" endpoint would extend.**

Elsewhere in the authed group: notifications, **videos** (full CRUD + comments + favorite — but no moderate/tag/collection actions today), regional-branches, branch members/news, resources, vacancies, resumes, events, **courses** (full CRUD + favorite/archive/unarchive).

## Migrations — relevant recent tables

- `2025_05_04_080620_create_videos_table.php` + follow-ups adding `video_link`, `external_link`
- `2025_06_23_034340_create_courses_table.php`, `create_teachers_table`, `create_semesters_table`, `create_lessons_table`, `create_course_teacher_table` — full education-module schema, directly transplantable pattern for Кинопросвет projects
- `2025_03_19_130635_create_favorites_table.php` — polymorphic favorites (reusable as-is)
- `2022_09_02_000500_create_moderations_table.php` — polymorphic moderation table (reusable as-is, `Moderatable` trait points here)
- `2025_12_05_103502_add_roles_to_users_table.php` + `2025_12_12_124141_migrate_role_to_roles.php` — roles migrated from single string to array; precedent for how a role addition/migration is typically shipped here

No tags/taxonomy table, no chat/message table, no video-transcoding/streaming-job table exist anywhere in `database/migrations/`.

## Gaps relevant to Кинопросвет scoping

1. **No taxonomy/tag model** — needed for п.5/п.6 (жанр, тегирование) — net-new table + pivot.
2. **No moderation on Video** — `Video` doesn't use `Moderatable` today; adding it is a small, well-trodden change (trait exists, just needs applying + migration + scope wiring).
3. **No collections/подборки model** — needed for п.2.3/п.3, net-new.
4. **No chat/websocket infra** — no Reverb/Pusher package installed, no message table.
5. **No streaming/transcoding infra** — Media Library serves files as-is; HLS/adaptive streaming is a from-scratch infra project, not a CRUD extension.
6. **Admin panel has no generic CRUD scaffold** — every new admin-manageable entity is hand-built per-controller (visible in how `UserAdminController` is the only admin controller today).
