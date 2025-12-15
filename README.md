# Donation DanaPeduli

Donation DanaPeduli adalah aplikasi **platform donasi online berbasis web** yang dibuat sebagai **proyek portofolio** untuk mendemonstrasikan kemampuan pengembangan aplikasi backend dan frontend, termasuk alur donasi, autentikasi pengguna, serta integrasi payment gateway.

> ⚠️ **DISCLAIMER PENTING**  
> Aplikasi ini dibuat **hanya untuk keperluan portofolio dan pembelajaran**.  
> **Bukan platform donasi resmi** dan **tidak menerima donasi sungguhan**.

---

## 📌 Deskripsi Proyek

Aplikasi ini mensimulasikan sistem donasi seperti platform donasi pada umumnya, di mana pengguna dapat melihat campaign, melakukan donasi, dan sistem akan memproses pembayaran menggunakan **Midtrans Testing / Sandbox**.

Proyek ini menitikberatkan pada:
- Alur bisnis donasi
- Integrasi payment gateway
- Pengelolaan data campaign dan donasi
- Arsitektur backend yang rapi dan terstruktur

---

## ✨ Fitur Utama

- Daftar campaign donasi
- Detail campaign
- Proses donasi
- Simulasi pembayaran menggunakan Midtrans
- Handling callback / notification pembayaran
- Dashboard admin untuk manajemen campaign

---

## 🔐 Fitur Pembayaran

Aplikasi ini menggunakan **Midtrans dalam mode Testing / Sandbox** untuk mensimulasikan proses pembayaran donasi.

- Tidak ada transaksi uang asli
- Semua payment menggunakan **Midtrans Sandbox**
- Data donasi bersifat dummy / simulasi
- Digunakan untuk mendemonstrasikan:
  - Alur pembayaran online
  - Generate token Midtrans
  - Handling callback / notification
  - Validasi status transaksi

> ⚠️ **Catatan:**  
> Jangan gunakan aplikasi ini sebagai platform donasi nyata.

---

## 🛠️ Tech Stack

**Backend**
- Laravel
- PHP
- PostgreSQL

**Admin Dashboard**
- Filament
  
**Frontend**
- React, Inertia

**Payment Gateway**
- Midtrans (Sandbox / Testing)

