# 🌱 KilimoStoreMS - Enterprise Agricultural Warehouse & Financial Management System

![KilimoStoreMS Banner](https://img.shields.io/badge/KilimoStore-Enterprise_System-10b981?style=for-the-badge&logo=laravel&logoColor=white)

**KilimoStoreMS** ni mfumo wa kisasa wa kiwango cha *Enterprise* ulioundwa maalum kwa ajili ya usimamizi wa maghala ya mazao ya kilimo, utoaji wa huduma za kuchakata mazao (kama kukoboa, kukausha, kupanga madaraja), na usimamizi wa kifedha ikiwemo mikopo na malipo kwa wakulima. 

Mfumo huu umejengwa kwa kutumia **Laravel API (Backend)** yenye nguvu sana, na muonekano wa **HTML/CSS/JS (Frontend)** uliobuniwa kwa umahiri ukiwa na uhuishaji (animations), responsiveness, na uzoefu bora sana kwa mtumiaji (Premium UX).

---

## ✨ Sifa Kuu za Mfumo (Key Features)

### 👥 1. Usimamizi wa Wakulima (Farmer Management)
* Usajili wa wakulima na taarifa zao (NIDA, Simu, Mkoa, n.k).
* *Profile Dashboard* ya kila mkulima inayoonyesha historia nzima ya mzigo, mikopo na huduma alizopata.
* **Server-side Pagination** ili kuhakikisha mfumo haulemewi hata ukiwa na wakulima laki moja.
* *Dynamic Avatars* (Picha za wasifu) zenye rangi na herufi zinazotengenezwa moja kwa moja kutokana na jina la mkulima.

### 📦 2. Udhibiti wa Mizigo & Maghala (Stock & Inventory)
* Kupokea mzigo ukiwa na uzito kamili (Kg, Tani) au **Uzito Usiojulikana** (mfano: Gunia za Mpunga usio kobolewa).
* Ubadilishaji wa vipimo (Unit conversions) kati ya Gunia, Roba, Kilo, Tani, n.k.
* Kufuatilia mizigo iliyohifadhiwa, inayosubiri huduma, au iliyochukuliwa.

### 🛠️ 3. Huduma & Uchakataji (Services Catalog)
* Usanidi wa huduma kama vile *Milling* (Kukoboa), *Drying* (Kukausha), *Cleaning*, n.k.
* **Crop-Specific Validation:** Mfumo una akili ya kuzuia huduma za zao moja kupewa zao lingine (Mfano: Hauruhusu huduma ya kukoboa mpunga itumike kwenye mzigo wa mahindi).
* Kuweka gharama za huduma kulingana na kizio (Unit Rate).

### 💰 4. Miamala ya Kifedha (Financial & Loans)
* Kutoa na kusimamia mikopo ya wakulima.
* Kufanya malipo (Settlement) ambapo makato yote (Gharama za kuhifadhi, mikopo, huduma) yanakatwa moja kwa moja kabla ya kumlipa mkulima.

---

## 🚀 Teknolojia Zilizotumika (Tech Stack)

* **Backend:** PHP 8.x, Laravel 11.x, Eloquent ORM, RESTful API
* **Frontend:** Vanilla HTML5, CSS3 (Custom Variables, Glassmorphism, Micro-animations), Vanilla JavaScript (ES6+), Fetch API
* **Database:** MySQL / SQLite
* **Architecture:** Multi-tenancy ready

---

## 💻 Namna ya Ku-Install Kwenye Kompyuta Yako (Local Setup)

Ili kuwasha mradi huu kwenye kompyuta yako *(Local Environment)*, fuata hatua hizi kwa umakini:

### Mahitaji (Prerequisites)
Hakikisha kompyuta yako ina:
* PHP (v8.2 au zaidi)
* Composer (Kwa ajili ya Laravel packages)
* MySQL Server (Au SQLite ikiwa unataka kutumia hifadhidata nyepesi)
* Git

### Hatua za Usakinishaji (Installation Steps)

**1. Pakua Mradi (Clone Repository)**
Fungua *terminal/CMD* na andika:
```bash
git clone https://github.com/BONIFACE6325/kilimoStoreMS.git
cd kilimoStoreMS
```

**2. Weka Dependencies (Install Packages)**
```bash
composer install
```

**3. Tengeneza Faili la Mazingira (Environment Setup)**
```bash
cp .env.example .env
```
*(Kwa watumiaji wa Windows CMD, tumia: `copy .env.example .env`)*

**4. Unganisha na Database**
Fungua faili la `.env` lililotengenezwa na uweke jina la database yako, username, na password (kama unatumia MySQL). 
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=kilimo_store_db
DB_USERNAME=root
DB_PASSWORD=
```
*Ikiwa unataka kutumia SQLite (njia rahisi zaidi), badilisha hapo juu iwe:*
```env
DB_CONNECTION=sqlite
# Kisha futa mistari ya DB_HOST, DB_PORT, DB_DATABASE, n.k.
```

**5. Tengeneza Key ya Usalama (Application Key)**
```bash
php artisan key:generate
```

**6. Panga Hifadhidata (Run Migrations)**
Hii itatengeneza *tables* zote kwenye Database yako.
```bash
php artisan migrate
```
*(Kama mfumo unakuuliza utengeneze database kwasababu haipo, bofya 'Yes')*

**7. Washa Seva ya Mfumo (Start the Development Server)**
```bash
php artisan serve
```

---

## 🌐 Jinsi ya Kutumia (Usage)

Baada ya kuwasha seva, fungua kivinjari (Browser) chochote (mf. Chrome) na uende kwenye kiungo hiki:

👉 **`http://localhost:8000/index.html`** *(Au `http://localhost:8000/login.html`)*

Kwa kuwa muonekano wote (UI) upo kwenye folda la `public/`, utaweza kuingiliana na API za mfumo moja kwa moja. Unaweza kwenda kwenye menyu ya **Farmers** kuanza kusajili wakulima na kupokea mizigo.

---

<div align="center">
  <b>Tengeneza mazingira bora ya kilimo. Kuza uchumi.</b><br>
  <i>Imejengwa na Timu ya KilimoStore</i>
</div>
