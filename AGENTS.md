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

- `db_setup.php` creates the database and five table schemas; `main.php` includes it. `db_connect.php` provides PDO. Sample rows and the required admin account are **not yet seeded in code**. / `db_setup.php` 已建立数据库和五张表，`main.php` 会调用它；`db_connect.php` 提供 PDO。示例数据和指定管理员账号**尚未由代码建立**。
- XAMPP PHP has `pdo_mysql`; `db_setup.php` ran successfully against the running MySQL service. Registration and login have passed syntax checks but have not yet been tested through HTTP. / XAMPP PHP 具备 `pdo_mysql`，`db_setup.php` 已在运行中的 MySQL 上成功执行；注册和登录已通过语法检查，但尚未通过 HTTP 实测。
- Part 2 code now uses the database: `process_registration.php` writes both tables in a transaction with a password hash; `login.php` checks that hash and stores `email`, `type`, and the existing `logged_in` flag in session. The existing form validation and page layout remain. / Part 2 代码现已改用数据库：注册以事务写入两张表并储存密码哈希；登录验证哈希，session 保存 `email`、`type` 和现有的 `logged_in` 标记；原有表单验证与版面保留。
- Registration redirects to `login.php` after success so the new account can sign in. No automatic import of the old `data/User/user.txt` was done. / 注册成功后跳转 `login.php` 以便登录；旧 `data/User/user.txt` 没有自动迁移。
- Existing `profile.php`, `update_profile.php`, and `activity_reg.php` still use `data/User/user.txt`. Other Assignment 2 pages and database integrations need a separate review and implementation. / 现有 `profile.php`、`update_profile.php` 和 `activity_reg.php` 仍使用 `data/User/user.txt`；其他 Assignment 2 页面与数据库整合仍须另行检查和完成。

## Next steps / 下一步

1. Seed all five tables and the required admin account, then test database creation. / 为五张表加入示例数据及指定管理员账号，并测试数据库建立。
2. Test registration, duplicate-email rejection, password hashing, correct/incorrect login, and session values with Apache and MySQL. / 使用 Apache 和 MySQL 测试注册、重复邮箱、密码哈希、正确与错误登录和 session 值。
3. Complete Parts 3 to 5, including user/admin access control and removal of remaining `user.txt` dependencies. / 完成第三至第五部分，包括用户/管理员权限控制及移除其余 `user.txt` 依赖。
4. Update `about.php`, add the unlisted demonstration video link, and test all relative links and required flows before submission. / 更新 `about.php`、加入不公开演示视频链接，并在提交前测试相对链接及所有必要流程。

Do not claim runtime completion from file inspection or PHP lint alone. / 不要仅凭文件检查或 PHP 语法检查声称运行成功。
