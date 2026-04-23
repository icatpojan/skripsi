<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class WasteTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $wasteTypes = [
            [
                'name' => 'Sampah Organik',
                'description' => 'Sampah yang berasal dari makhluk hidup seperti sisa makanan, daun, dll',
                'icon' => 'fas fa-leaf',
                'color' => '#28a745',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Sampah Anorganik',
                'description' => 'Sampah yang tidak dapat terurai seperti plastik, kaca, logam',
                'icon' => 'fas fa-recycle',
                'color' => '#007bff',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Sampah B3',
                'description' => 'Sampah berbahaya dan beracun seperti baterai, obat-obatan',
                'icon' => 'fas fa-exclamation-triangle',
                'color' => '#dc3545',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Sampah Elektronik',
                'description' => 'Sampah peralatan elektronik seperti HP, laptop, TV',
                'icon' => 'fas fa-laptop',
                'color' => '#6f42c1',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Sampah Konstruksi',
                'description' => 'Sampah dari bangunan seperti batu, semen, kayu',
                'icon' => 'fas fa-hammer',
                'color' => '#fd7e14',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Sampah Lainnya',
                'description' => 'Jenis sampah lainnya yang tidak termasuk kategori di atas',
                'icon' => 'fas fa-trash',
                'color' => '#6c757d',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('waste_types')->insert($wasteTypes);
    }
}
