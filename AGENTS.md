# COS30020 Assignment 2 / COS30020 作业 2

## Source and scope / 要求来源与范围

- Follow `Assignment 2 [Brief & Marking Scheme].pdf`. Continue the Assignment 1 Boardgame Hub site, keeping its page names, root-level PHP files, folder structure, general design, and relative links.
- 以 `Assignment 2 [Brief & Marking Scheme].pdf` 为准。继续制作 Assignment 1 的 Boardgame Hub，保留页面名称、根目录 PHP 文件、文件夹结构、整体设计和相对链接。

## Required work / 作业要求

1. **Part 1 / 第一部分：** Create the MySQL database and five tables automatically, with at least four sample records per table and an admin account (`admin@swin.edu.my`, password `admin`). Passwords must be hashed. / 自动建立数据库及五张表，每张表至少四条示例数据，并建立指定管理员账号；密码必须哈希储存。
2. **Part 2 / 第二部分：** Registration checks duplicate email, hashes the password, and writes `user_table` and `account_table`. Login verifies the hash and stores email and account type in the session before redirecting to `main_menu.php`. / 注册时数据库查重、哈希密码并写入两张表；登录时验证哈希，保存邮箱和账号类型到 session，然后跳转主菜单。
3. **Part 3 / 第三部分：** Gate user pages by session type `user`; read activities, community details, and profile from the database; update profile and upload validated photo/PDF; add a pending smart-feature contribution with a PDF. / 以 `user` 类型限制用户页面；活动、社区详情、个人资料改读数据库；实现资料更新及照片/PDF 上传；新增待审核的智能功能投稿及 PDF。
4. **Part 4 / 第四部分：** Build a distinct admin menu, account management, community and activity approval management, and an About report with a video link. / 制作不同样式的管理员菜单、账号管理、社区和活动审核管理，以及含视频链接的 About 报告。
5. **Part 5 / 第五部分：** Build a smart feature using approved database entries, showing name, category, image, and downloadable PDF; handle no match. / 用已批准的数据库记录制作智能功能，显示名称、类别、图片和可下载 PDF，并处理无匹配结果。

Use prepared SQL statements and server-side session/upload validation. Required upload folders include `profile_images/`, `resume/`, and `smart_feature_description/`. / 使用预处理 SQL 和服务器端 session/上传验证；需要上述三个上传目录。

## Current progress / 当前进度

- `db_setup.php` creates the database and five table schemas; `main.php` includes it. `db_connect.php` provides PDO. Activities and community rows are imported, but the requirement of four samples in **every** table and the required admin account are still incomplete. / `db_setup.php` 建立数据库及五张表，`main.php` 调用它；`db_connect.php` 提供 PDO。活动和社区记录已导入，但**每张表**至少四条样本和指定管理员账号仍未完成。
- XAMPP PHP has `pdo_mysql`; `db_setup.php` ran against MySQL. Registration and a correct user login were tested through HTTP on 4 November 2026. Duplicate-email rejection, incorrect login, and admin login remain untested. / XAMPP PHP 具备 `pdo_mysql`，`db_setup.php` 已在 MySQL 上执行。2026 年 11 月 4 日通过 HTTP 测试了注册和正确的用户登录；重复邮箱、错误密码及管理员登录仍未实测。
- Part 2 code now uses the database: `process_registration.php` writes both tables in a transaction with a password hash; `login.php` checks that hash and stores `email`, `type`, and the existing `logged_in` flag in session. The existing form validation and page layout remain. / Part 2 代码现已改用数据库：注册以事务写入两张表并储存密码哈希；登录验证哈希，session 保存 `email`、`type` 和现有的 `logged_in` 标记；原有表单验证与版面保留。
- Registration redirects to `login.php` after success so the new account can sign in. No automatic import of the old `data/User/user.txt` was done. / 注册成功后跳转 `login.php` 以便登录；旧 `data/User/user.txt` 没有自动迁移。
- Part 3 is implemented. `main_menu.php`, `activities.php`, `community_detail.php`, `profile.php`, `update_profile.php`, and `activity_reg.php` require a `user` session. `activities.php`, `community.php`, and `community_detail.php` read database rows; `activity_reg.php` writes pending registrations to the database. The old `user.txt` dependency has been removed from these pages. / 第三部分已实现：上述用户页面检查 `user` session；活动、社区列表与详情从数据库读取，活动报名写入待审核记录；这些页面已不再依赖旧 `user.txt`。
- `db_setup.php` adds the Part 3 columns and imports the 12 existing JSON activities and 8 community records without duplicate imports on repeated runs. It preserves existing database rows. `user_table.resume_file` stores the supporting PDF path. / `db_setup.php` 增加第三部分所需字段，导入原有 12 项活动及 8 项社区内容；重复执行不会重复导入，并保留原有数据库记录。辅助 PDF 的路径保存在 `user_table.resume_file`。
- `update_profile.php` loads and updates the user's database profile, including email changes that cascade to `account_table`. It validates JPG/JPEG/PNG photos (up to 5MB) and PDF supporting documents (up to 7MB). `smart_feature_add.php` accepts a board game name, category, required PDF, and optional JPG/PNG image, then saves a `pending` row. / 资料更新页从数据库读取和保存资料，邮箱变更通过外键同步到账户表；照片及辅助 PDF 均在服务器端验证。新增投稿页保存名称、类别、必需 PDF、可选图片及 `pending` 状态。
- Part 3 HTTP/MySQL checks passed on 4 November 2026: anonymous redirects, user page access, database content, profile/email/photo/PDF update, pending activity registration, duplicate-registration rejection, pending smart contribution, and invalid-format upload rejection. Temporary test records and uploads were removed. Apache and MySQL were started for testing. / 2026 年 11 月 4 日已通过上述第三部分的 HTTP/MySQL 实测，测试账户、记录和上传文件已清理；测试时启动了 Apache 与 MySQL。
- Database logic remains separate from page UI. `activities.php` and `activity_reg.php` share `activities_db.php`; `community.php` and `community_detail.php` share `community_db.php`, with the detail-only login check preserved. Profile, profile update, login, and smart contribution keep their own root-level `*_db.php` files. Original page URLs and relative links remain. `registration_db.php` holds registration processing; `process_registration.php` remains the original POST entry point. / 数据库逻辑与页面 UI 分离：活动列表和报名共用 `activities_db.php`，社区列表和详情共用 `community_db.php`，详情页仍单独检查登录。资料、资料更新、登录和智能投稿保留各自的根目录 `*_db.php`；原页面网址和相对链接不变。注册逻辑位于 `registration_db.php`，原 `process_registration.php` POST 入口保留。
- After the shared-file merge, HTTP tests confirmed anonymous access rules, database-backed list/detail content, pending activity registration, duplicate rejection, and missing-community handling; temporary test records were removed. / 合并共享文件后，HTTP 实测确认了访客权限、数据库列表与详情内容、待审核活动报名、重复报名拒绝及不存在社区的处理；临时测试记录已清理。

## Next steps / 下一步

1. Seed all five tables and the required admin account, then test database creation. / 为五张表加入示例数据及指定管理员账号，并测试数据库建立。
2. Finish Part 2 checks: duplicate-email rejection, password hashing, incorrect login, admin login, and session values. / 完成第二部分剩余测试：重复邮箱、密码哈希、错误登录、管理员登录及 session 值。
3. Complete Parts 4 and 5, including the admin menu/management pages and the approved-only smart feature results page. / 完成第四、第五部分，包括管理员菜单与管理页面，以及只显示已批准记录的智能功能结果页。
4. Update `about.php`, add the unlisted demonstration video link, and test all relative links and required flows before submission. / 更新 `about.php`、加入不公开演示视频链接，并在提交前测试相对链接及所有必要流程。

Do not claim runtime completion from file inspection or PHP lint alone. / 不要仅凭文件检查或 PHP 语法检查声称运行成功。
