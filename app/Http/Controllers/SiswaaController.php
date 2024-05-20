<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use App\Imports\UserImport;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\SettingWaktu;
use Carbon\Carbon;

class SiswaaController extends Controller
{
    public function siswaa(Request $request)
    {
        // Mengambil semua data user dengan level siswa
        $search = $request->search; 
        $users = User::where('level', 'siswa')->paginate(10);
$settings = SettingWaktu::all();

            $expired = false;
    foreach ($settings as $setting) {
        if (Carbon::now()->greaterThanOrEqualTo($setting->waktu)) {
            $expired = true;
            break;
        }
    }
        // Meneruskan data ke tampilan
        return view('halaman.siswaa', compact('users','expired','settings'));
}

public function destroy($id)
{
    try {
        $user = User::find($id);
        
        if ($user) {
            $user->forceDelete(); // Menghapus data secara permanen
            return redirect('/siswaa')->with('uccess', 'Data berhasil dihapus secara permanen');
        } else {
            return redirect('/siswaa')->with('error', 'Data tidak ditemukan.');
        }
    } catch (\Exception $e) {
        return redirect('/siswaa')->with('error', 'Gagal menghapus data. Silakan coba lagi.');
    }
}



    public function add_siswaa()
    {
           $settings = SettingWaktu::all();

            $expired = false;
    foreach ($settings as $setting) {
        if (Carbon::now()->greaterThanOrEqualTo($setting->waktu)) {
            $expired = true;
            break;
        }
    }
        // Meneruskan data ke tampilan
        return view('tambah.add_siswaa', compact('expired','settings'));
    }

    public function store(Request $request)
{
    $request->validate([
        'name' => ['required', 'min:3', 'max:30'],
        'level' => 'required',
        'kelas' => 'required',
        'email' => 'required|unique:users,email',
        'password' => ['required', 'min:8', 'max:12'],
    ]);


        $user = User::where('name', $request->name)->orWhere('email', $request->email)->first();
        if ($user) {
            // Jika nama atau email sudah digunakan, tampilkan pesan kesalahan
            return back()->withInput()->with('error', 'Nama atau email sudah digunakan.');
        }

        DB::table('users')->insert([
            'name' => $request->name,
            'level' => $request->level,
            'kelas' => $request->kelas,
            'email' => $request->email,
            'password' => bcrypt($request->password),
        ]);

        // Redirect dengan pesan sukses
        return redirect('/siswaa')->with('success', 'Data Berhasil Ditambahkan');
   

}


    public function edit($id)
{
    $siswaa = User::find($id);
    // Jangan mengirimkan password ke tampilan
    unset($siswaa->password);
  $settings = SettingWaktu::all();

            $expired = false;
    foreach ($settings as $setting) {
        if (Carbon::now()->greaterThanOrEqualTo($setting->waktu)) {
            $expired = true;
            break;
        }
    }

    return view('edit.edit_siswaa', compact('settings', 'expired','siswaa'));
}

public function update(Request $request, $id)
{
    $siswaa = User::find($id);

    $request->validate([
        'name' => ['required', 'min:3', 'max:30'],
        'level' => 'required',
        'email' => 'required|email|unique:users,email,' . $siswaa->id,
        'kelas' => 'required',
        'password' => ['nullable', 'min:8', 'max:12'], // Mengubah menjadi nullable
    ]);

    $data = [
        'name' => $request->name,
        'level' => $request->level,
        'email' => $request->email,
        'kelas' => $request->kelas,
        
    ];

    // Menambahkan password ke data hanya jika ada input password
    if ($request->filled('password')) {
        $data['password'] = bcrypt($request->password);
    }

    $siswaa->update($data);

    return redirect('/siswaa')->with('update_success', 'Data Berhasil Diupdate');
}
    public function search(Request $request)
    {
        // Dapatkan input pencarian
        $searchTerm = $request->input('search');

        // Lakukan pencarian hanya jika input tidak kosong
        if (!empty($searchTerm)) {
            // Validasi input
            $request->validate([
                'search' => 'string', // Sesuaikan aturan validasi sesuai kebutuhan Anda
            ]);

            // Lakukan pencarian dengan mempertimbangkan validasi input, level 'siswa', dan status_pemilihan
            $users = User::where('level', 'siswa')
                        ->where(function ($query) use ($searchTerm) {
                            $query->where('name', 'like', "%{$searchTerm}%")
                                ->orWhere('status_pemilihan', 'like', "%{$searchTerm}%"); // Ubah sesuai dengan tipe data status_pemilihan
                        })
                        ->get();
        } else {
            // Jika input kosong, ambil semua data user dengan level 'siswa'
            $users = User::where('level', 'siswa')->get();
        }

        // Memberikan respons berdasarkan hasil pencarian
        return response()->json($users);
    }

    public function siswaimportexcel(Request $request) {
        // Menghapus semua data siswa dari database secara permanen
        User::query()->where('level','siswa')->forceDelete();
    
        // Memproses file Excel yang diunggah
        $file = $request->file('file');
        $namafile = $file->getClientOriginalName();
        $file->move('DataSiswa', $namafile);
    
        // Melakukan impor data dari file Excel yang baru
        Excel::import(new UserImport, public_path('/DataSiswa/'.$namafile));
    
        // Redirect ke halaman siswaa dengan pesan sukses
        return redirect('/siswaa')->with('success', 'Data Berhasil Ditambahkan');
    }
}