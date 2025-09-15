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
  - ლოგირება: Framework-ის სტანდარტული ლოგები გამორთულია; მხოლოდ ახალი მომხმარებლის შექმნა ილოგება კასტომ ფაილში.

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

   sudo mysql -u tornike -p < database/001_create_database.sql \
     && mysql -u tornike -p < database/002_create_users_table.sql \
     && mysql -u tornike -p < database/003_seed_admin.sql \
     && mysql -u tornike -p < database/004_seed_dummy_users.sql

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

   php -S localhost:8000


***********************************
ლოგები — ახალი მომხმარებლის შექმნა
***********************************

- Framework-ის ლოგირება გამორთულია: `application/config/config.php` → `log_threshold = 0`
- მხოლოდ ახალი მომხმარებლის შექმნისას იწერება ჩანაწერი კასტომ ლოგში:
  - ფაილი: `application/logs/user_creation/user_creation-YYYY-MM-DD.log`
  - ფორმატი: `YYYY-MM-DD HH:MM:SS | id=<ID> | name=<NAME> | email=<EMAIL> | phone=<PHONE> | temp_password=<TEMP>`
  - შენიშვნა: დროებითი პაროლი ილოგება მოთხოვნით; გამოიყენეთ მხოლოდ უსაფრთხო გარემოში
- იმპლემენტაცია: `application/libraries/User_service.php` → `write_user_creation_log()`
- ელფოსტა ყოველთვის იგზავნება SMTP-ით; 
- ელფოსტის გატესტვა შეგიძლიათ Mailtrap-ის სერვისის საშუალებით, როდესაც შეხვალთ და შექმნით sandbox-ს Code Samples სექციაში აირჩიეთ CodeIgniter და მონაცემები დააკპირეთ .env ფაილში.