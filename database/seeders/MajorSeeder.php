<?php

namespace Database\Seeders;

use App\Models\Major;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class MajorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $majors = [
            [
                'code' => 'KTPM13A',
                'name' => 'Kỹ thuật phần mềm',
                'note' => 'Chuyên ngành kỹ thuật phần mềm',
            ],
            [
                'code' => 'QTKD22A',
                'name' => 'Quản trị kinh doanh',
                'note' => 'Chuyên ngành quản trị kinh doanh',
            ],
            [
                'code' => 'HTTT22A',
                'name' => 'Hệ thống thông tin',
                'note' => 'Chuyên ngành hệ thống thông tin',
            ]
        ];

        foreach ($majors as $major) {
            Major::create($major);
        }
    }
}
