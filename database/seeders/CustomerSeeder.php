<?php

namespace Database\Seeders;

use App\Domains\Customer\Models\Customer;
use Illuminate\Database\Seeder;

class CustomerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        Customer::create([
            'id' => 5,
            'name' => 'Ahmet Aslan Batman',
            'email' => 'ahmet@ahmet.com',
            'phone' => '05433695162',
            'debt' => '0',
            'address' => 'Batman',
            'created_at' => '2025-02-08 11:52:51',
            'updated_at' => '2025-02-08 12:00:06',
        ]);

        Customer::create([
            'id' => 11,
            'name' => 'İlhan Koton(Bitlis)',
            'email' => 'ilhan@gmail.com',
            'phone' => '05424170002',
            'debt' => '0',
            'address' => 'Bitlis',
            'created_at' => '2025-02-08 12:00:55',
            'updated_at' => '2025-02-08 12:00:55',
        ]);

        Customer::create([
            'id' => 6,
            'name' => 'Ahmet Abi Poşetçi',
            'email' => 'ahmetabi@gmail.com',
            'phone' => '05348805921',
            'debt' => '0',
            'address' => 'Adana',
            'created_at' => '2025-02-08 11:53:56',
            'updated_at' => '2025-02-08 11:53:56',
        ]);

        Customer::create([
            'id' => 7,
            'name' => 'Cengiz Aslan Deniz Plastik',
            'email' => 'cengiz@cengiz.com',
            'phone' => '05336421998',
            'debt' => '0',
            'address' => 'Adana',
            'created_at' => '2025-02-08 11:55:12',
            'updated_at' => '2025-02-08 11:55:12',
        ]);

        Customer::create([
            'id' => 8,
            'name' => 'Hanifi Aslan  Deniz Plastik',
            'email' => 'hanifi@gmail.com',
            'phone' => '05324758001',
            'debt' => '0',
            'address' => 'Adana',
            'created_at' => '2025-02-08 11:55:57',
            'updated_at' => '2025-02-08 12:30:27',
        ]);

        Customer::create([
            'id' => 9,
            'name' => 'Cuma Maraş',
            'email' => 'cuma@gmail.com',
            'phone' => '05432664604',
            'debt' => '0',
            'address' => 'Maraş',
            'created_at' => '2025-02-08 11:59:10',
            'updated_at' => '2025-02-08 11:59:10',
        ]);

        Customer::create([
            'id' => 10,
            'name' => 'Donat Ambalaj Necat',
            'email' => 'donatambalaj@gmail.com',
            'phone' => '05333463216',
            'debt' => '0',
            'address' => 'Bursa',
            'created_at' => '2025-02-08 11:59:57',
            'updated_at' => '2025-02-08 11:59:57',
        ]);

        Customer::create([
            'id' => 12,
            'name' => 'Mayacı Faruk',
            'email' => 'faruk@gmail.com',
            'phone' => '05325589652',
            'debt' => '0',
            'address' => 'Adana',
            'created_at' => '2025-02-08 12:01:53',
            'updated_at' => '2025-02-08 12:01:53',
        ]);

        Customer::create([
            'id' => 13,
            'name' => 'Hasan Abi Mersin',
            'email' => 'hasan@hasan.com',
            'phone' => '05359747011',
            'debt' => '0',
            'address' => 'Mersin',
            'created_at' => '2025-02-08 12:03:09',
            'updated_at' => '2025-02-08 12:03:09',
        ]);

        Customer::create([
            'id' => 14,
            'name' => 'Memet Can Poşetçi',
            'email' => 'memetcan@gmail.com',
            'phone' => '05538682394',
            'debt' => '0',
            'address' => 'Adana',
            'created_at' => '2025-02-08 12:04:50',
            'updated_at' => '2025-02-08 12:04:50',
        ]);

        Customer::create([
            'id' => 15,
            'name' => 'Osman Şener Malatya',
            'email' => 'osman@gmail.com',
            'phone' => '05369471996',
            'debt' => '0',
            'address' => 'Malatya',
            'created_at' => '2025-02-08 12:05:59',
            'updated_at' => '2025-02-08 12:05:59',
        ]);

        Customer::create([
            'id' => 16,
            'name' => 'Ömer Öner',
            'email' => 'ömer@gmail.com',
            'phone' => '05387742701',
            'debt' => '0',
            'address' => 'Adana',
            'created_at' => '2025-02-08 12:06:42',
            'updated_at' => '2025-02-08 12:06:42',
        ]);

        Customer::create([
            'id' => 17,
            'name' => 'Orhan Can Ambalaj Ağrı',
            'email' => 'orhancan@gmail.com',
            'phone' => '05000000000',
            'debt' => '0',
            'address' => 'Ağrı',
            'created_at' => '2025-02-08 12:07:28',
            'updated_at' => '2025-02-08 12:07:28',
        ]);

        Customer::create([
            'id' => 18,
            'name' => 'Remzi Çatalca YağmurPlatik',
            'email' => 'remzi@gmail.com',
            'phone' => '05342343760',
            'debt' => '0',
            'address' => 'Adana',
            'created_at' => '2025-02-08 12:08:26',
            'updated_at' => '2025-02-08 12:08:26',
        ]);

        Customer::create([
            'id' => 19,
            'name' => 'Reyis Abi',
            'email' => 'reyis@gmail.com',
            'phone' => '05523950115',
            'debt' => '0',
            'address' => 'Adana',
            'created_at' => '2025-02-08 12:09:01',
            'updated_at' => '2025-02-08 12:09:01',
        ]);

        Customer::create([
            'id' => 20,
            'name' => 'Saddam Pazarcı',
            'email' => 'saddam@gmail.com',
            'phone' => '05345664744',
            'debt' => '0',
            'address' => 'Adana',
            'created_at' => '2025-02-08 12:10:29',
            'updated_at' => '2025-02-08 12:10:29',
        ]);

        Customer::create([
            'id' => 21,
            'name' => 'Tahir Abi Tarsus',
            'email' => 'tahir@gmail.com',
            'phone' => '05327979150',
            'debt' => '0',
            'address' => 'Tarsus',
            'created_at' => '2025-02-08 12:11:26',
            'updated_at' => '2025-02-08 12:41:06',
        ]);

        Customer::create([
            'id' => 22,
            'name' => 'Vedat Ergül',
            'email' => 'vedat@gmail.com',
            'phone' => '05321788549',
            'debt' => '0',
            'address' => 'Aydın',
            'created_at' => '2025-02-08 12:12:27',
            'updated_at' => '2025-02-08 12:12:27',
        ]);

        Customer::create([
            'id' => 23,
            'name' => 'resit ergül',
            'email' => 'reşit@gmail.com',
            'phone' => '05555555555',
            'debt' => '0',
            'address' => 'Adana',
            'created_at' => '2025-02-08 13:33:39',
            'updated_at' => '2025-02-08 13:33:39',
        ]);

        Customer::create([
            'id' => 28,
            'name' => 'Dilek Mersin',
            'email' => 'dilekmersin@gmail.com',
            'phone' => '00000000000',
            'debt' => '',
            'address' => 'Mersin',
            'created_at' => null,
            'updated_at' => null,
        ]);

        Customer::create([
            'id' => 27,
            'name' => 'Abdullah Antalya',
            'email' => 'abdullahantalya@gmail.com',
            'phone' => '05424685438\r\n',
            'debt' => '',
            'address' => '',
            'created_at' => null,
            'updated_at' => null,
        ]);

        Customer::create([
            'id' => 29,
            'name' => 'Seken Uğur ',
            'email' => 'sekenugur@gmail.com',
            'phone' => '00000000000',
            'debt' => '0',
            'address' => '',
            'created_at' => null,
            'updated_at' => null,
        ]);

        Customer::create([
            'id' => 33,
            'name' => 'kök ambalaj memet',
            'email' => 'iskender1@gmail.com',
            'phone' => '05433695162',
            'debt' => '0',
            'address' => 'Adana',
            'created_at' => '2025-04-10 10:24:00',
            'updated_at' => '2025-04-10 10:24:00',
        ]);

        Customer::create([
            'id' => 34,
            'name' => 'Ramazan malatya',
            'email' => 'ramazan-malatya@hotmail.com',
            'phone' => '05555555555',
            'debt' => '0',
            'address' => 'Malatya',
            'created_at' => '2025-04-23 12:54:21',
            'updated_at' => '2025-04-23 12:54:21',
        ]);

        Customer::create([
            'id' => 35,
            'name' => 'emır ambalaj',
            'email' => 'emırambalaj@gmail.com',
            'phone' => '05324758001',
            'debt' => '0',
            'address' => 'Adana',
            'created_at' => '2025-04-26 08:24:31',
            'updated_at' => '2025-04-26 08:24:31',
        ]);
    }
}
