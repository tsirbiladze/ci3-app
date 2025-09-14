********************
მოკლე აღწერა 
********************

- მიზანი: მარტივი users CRUD აპლიკაცია CodeIgniter 3-ზე, სუფთა არქიტექტურით და კარგი UX-ით.

- არქიტექტურა (MVC)
  - სტანდარტული CI3 სტრუქტურა: Controllers / Models / Views
  - `MY_Controller` - საერთო ლეიაუთი და JSON ჰელფერები (`json_success`/`json_error`)
  - `Authenticated_Controller` — ავტორიზაციის შემოწმება დაცულ გვერდებზე

- უსაფრთხოება
  - პაროლები: `password_hash()` / `password_verify()`
  - სესიები: CI Session; CSRF ჩართულია (ტოკენი ავტომატურად ერთვის `assets/app.js`-ის საშუალებით)
  - კონფიგი: `cookie_httponly = TRUE`, `sess_regenerate_destroy = TRUE`
  - დეველოპმენტი: დროებითი პაროლი ილოგება ნაცვლად ფოსტაზე გაგზავნისა (იხ. ქვემოთ „ლოგები — დროებითი პაროლი“);

- CRUD / UI (AJAX + Bootstrap)
  - ყველა ოპერაცია AJAX-ით, მოდალებში (Create/Edit/Delete)
  - DataTables server-side - sortable/searchable, მასშტაბირებადი სია
  - ერთიანი API ფორმატი: `{success, message, data, errors}`
  - საკუთარი თავის წაშლა დაბლოკილია (სერვერი + UI)

- სერვისები და მოდელები
  - `User_service` - მომხმარებლის შექმნა დროებითი პაროლით და შეტყობინება
  - `User_model` - პაგინაცია/ძიება/დათვლა DataTables-ისთვის

- Dashboard
  - `Total users` ითვლება `recordsTotal` ველიდან (server-side endpoint)

- დოკუმენტაცია / მომავალი ნაბიჯები
  - README: გაშვება/კონფიგი/აღწერა
  - მომავალი: მიგრაციები/სიდები

**********
მიგრაციები
**********

SQL ფაილები მდებარეობს `database/` დირექტორიაში:

- `database/001_create_database.sql` - ქმნის `ci_app` ბაზას
- `database/002_create_users_table.sql` - ქმნის `users` ცხრილს
- `database/003_seed_admin.sql` - ამატებს ადმინ მომხმარებელს
- `database/004_seed_dummy_users.sql` - ამატებს 100 მომხმარებელს

გაშვება (ერთხაზიანი):

.. code-block:: bash

   sudo mysql -u USERNAME -p PASSWORD < database/001_create_database.sql \
     && mysql -u USERNAME -p PASSWORD < database/002_create_users_table.sql \
     && mysql -u USERNAME -p PASSWORD < database/003_seed_admin.sql \
     && mysql -u USERNAME -p PASSWORD < database/004_seed_dummy_users.sql

***************
ადმინის მონაცემები
***************

- Email: `admin@example.com`
- პაროლი: `password`

*******************
გაშვება ნულიდან (clone → run)
*******************

1) დეფენდენსები

.. code-block:: bash

   composer install

2) ბაზის მომზადება (აირჩიეთ ერთ-ერთი)

.. code-block:: bash

   # პირდაპირ SQL-ებით
   sudo mysql -u USERNAME -p PASSWORD < database/001_create_database.sql \
     && mysql -u USERNAME -p PASSWORD < database/002_create_users_table.sql \
     && mysql -u USERNAME -p PASSWORD < database/003_seed_admin.sql \
     && mysql -u USERNAME -p PASSWORD < database/004_seed_dummy_users.sql

3) კონფიგურაცია (DB + SMTP)

კოპირება `env.example` ფაილისგან:

.. code-block:: bash

   cp env.example .env

შეავსეთ თქვენი database და SMTP კრედენციალები `.env` ფაილში:

.. code-block:: bash

   DB_HOST=localhost
   DB_USER=your_mysql_user
   DB_PASS=your_mysql_password
   DB_NAME=ci_app
   
   # SMTP (Mailtrap მაგალითი)
   SMTP_HOST=sandbox.smtp.mailtrap.io
   SMTP_PORT=2525
   SMTP_USER=your_mailtrap_username
   SMTP_PASS=your_mailtrap_password
   SMTP_CRYPTO=tls

4) აპლიკაციის გაშვება

.. code-block:: bash

   php -S localhost:8000 -t .

გახსენება: Base URL არის `http://localhost:8000/index.php`

***********************
ლოგები — დროებითი პაროლი
***********************

- სად ინახება ლოგები: `application/logs/log-YYYY-MM-DD.php`
- როდის ილოგება: როცა ადმინი ქმნის იუზერს ან იუზერი რეგისტრირდება
- სად ხდება ლოგირება: `application/libraries/User_service.php` → `notify_temp_password()`
  - დეველოპმენტ გარემოში (`ENVIRONMENT === 'development'`) იწერება `log_message('info', ...)`
  - არსებული ქრედენშალებით იგზავნება ელფოსტა SMTP-ით (Mailtrap ან თქვენი SMTP-ით)