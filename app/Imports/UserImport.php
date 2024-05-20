<?php

namespace App\Imports;

use App\Models\Siswa;
use App\Models\User;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Facades\Hash;

class UserImport implements ToModel
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        return new User([
            'id'=> $row[0],
            'name'=> $row[1],
            'level'=> $row[2],
            'kelas'=> $row[3],
            'email'=> $row[4],
            'password'=>bcrypt($row[5]),
            // 'updated_at'=>$row[6],
            // 'created_at'=>$row[7],


        ]);
    }
}
