# GIS MBSJ

Sistem Maklumat Geografi untuk Majlis Bandaraya Subang Jaya (MBSJ). Digunakan untuk merekod, memantau dan memaparkan laporan lapangan berasaskan lokasi GIS.

Dibina dengan **Laravel**, **Livewire** dan peta interaktif (Leaflet).

## Peranan Pengguna

| Peranan | Fungsi |
|---------|--------|
| **Superadmin** | Urus unit, pengguna, kategori; Hub Engineering; pantau semua laporan |
| **Surveyor / Vendor** | Cipta & hantar laporan lapangan + GIS (akaun vendor sedia ada) |
| **TA** | Lawatan tapak mengikut unit masing-masing |
| **Engineer** | Semak & sahkan laporan unit sendiri |
| **Pengarah** | Lulus / tolak; lihat Hub Engineering semua unit |

## Workflow

```text
Surveyor Hantar → TA Lawatan Tapak → Engineer Sahkan → Pengarah Lulus → Selesai / PDF
```

## Modul Utama

- Dashboard mengikut peranan & unit (TA/Engineer bertema unit)
- Hub Engineering (7 unit) untuk Superadmin & Pengarah
- Unit Engineering (CRUD)
- Laporan GIS + lampiran
- Lawatan Tapak (borang digital MBSJ)
- Pengesahan Engineer & Kelulusan Pengarah
- Audit trail + PDF rasmi
- Peta Interaktif (filter unit)

## Unit Engineering

Aktif (fasa semasa):

| Kod | Nama |
|-----|------|
| SAL-CERUN | Saliran & Cerun (Sinkhole / Cerun Runtuh) |
| JLN | Jalan |
| STR | Structure |
| ME | M&E |

Modul penuh kini: **Saliran & Cerun** (`/saliran-cerun`).

Akaun uji modul:

| Email | Role | Password |
|-------|------|----------|
| surveyor.saliran-cerun@mbsj.gov.my | Surveyor | password |
| ta.saliran-cerun@mbsj.gov.my | TA | password |
| engineer.saliran-cerun@mbsj.gov.my | Engineer | password |

Seed:

```bash
php artisan migrate
php artisan db:seed --class=SaliranCerunCategorySeeder
php artisan db:seed --class=SaliranCerunStaffSeeder
```

## Setup Ringkas

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
npm install && npm run build
php artisan serve
```

Refresh data demo:

```bash
php artisan db:seed --class=WorkflowDemoSeeder
```

## Akaun Demo (selepas seed)

Password semua: `password`

| Email | Role | Unit |
|-------|------|------|
| admin@mbsj.gov.my | Superadmin | — |
| surveyor@mbsj.gov.my | Surveyor (vendor) | Jalan |
| director@mbsj.gov.my | Pengarah | — |
| ta@mbsj.gov.my | TA | Jalan |
| engineer@mbsj.gov.my | Engineer | Jalan |
| surveyor.saliran-cerun@mbsj.gov.my | Surveyor | Saliran & Cerun |
| ta.saliran-cerun@mbsj.gov.my | TA | Saliran & Cerun |
| engineer.saliran-cerun@mbsj.gov.my | Engineer | Saliran & Cerun |
| ta.structure@mbsj.gov.my | TA | Structure |
| engineer.structure@mbsj.gov.my | Engineer | Structure |
| ta.me@mbsj.gov.my | TA | M&E |
| engineer.me@mbsj.gov.my | Engineer | M&E |

Login TA/Engineer unit berbeza → dashboard bertema & data unit tersebut. Superadmin/Pengarah: menu **Engineering** → pilih unit.

## Teknologi

- PHP 8.x / Laravel / Livewire
- Vite + Bootstrap (Vuexy)
- Leaflet (peta GIS)
- DomPDF (laporan rasmi)
- MySQL

---

Dibangunkan oleh [VinculoTech](https://vinculotech.com)
