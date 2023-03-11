<?php

namespace App\Controllers;

use App\Models\AkunModel;
use App\Models\KategoriAkunModel;
use CodeIgniter\RESTful\ResourcePresenter;
use \Hermawan\DataTables\DataTable;

class Akun extends ResourcePresenter
{
    public function index()
    {
        return view('akun/listAkun/index');
    }


    public function getDataAkun()
    {
        if ($this->request->isAJAX()) {

            $modelAkun = new AkunModel();
            $data = $modelAkun->where(['deleted_at' => null])->select('id, kode, nama');

            return DataTable::of($data)
                ->addNumbering('no')
                ->add('aksi', function ($row) {
                    return '
                    <a title="Detail" class="px-2 py-0 btn btn-sm btn-outline-dark" onclick="showModalDetail(' . $row->id . ')">
                        <i class="fa-fw fa-solid fa-magnifying-glass"></i>
                    </a>

                    <a title="Edit" class="px-2 py-0 btn btn-sm btn-outline-primary" onclick="showModalEdit(' . $row->id . ')/edit">
                        <i class="fa-fw fa-solid fa-pen"></i>
                    </a>

                    <form id="form_delete" method="POST" class="d-inline">
                        ' . csrf_field() . '
                        <input type="hidden" name="_method" value="DELETE">
                    </form>
                    <button onclick="confirm_delete(' . $row->id . ')" title="Hapus" type="button" class="px-2 py-0 btn btn-sm btn-outline-danger"><i class="fa-fw fa-solid fa-trash"></i></button>
                    ';
                }, 'last')
                ->toJson(true);
        } else {
            return "Tidak bisa load data.";
        }
    }


    public function show($id = null)
    {
        if ($this->request->isAJAX()) {

            $modelAkun = new AkunModel();
            $kategori  = $modelAkun->getAkun($id);
            $akun      = $modelAkun->find($id);

            $data = [
                'akun'      => $akun,
                'kategori'  => $kategori
            ];

            $json = [
                'data'   => view('akun/listAkun/show', $data),
            ];

            echo json_encode($json);
        } else {
            return 'Tidak bisa load data';
        }
    }


    public function new()
    {
        if ($this->request->isAJAX()) {

            $modelAkun = new AkunModel();
            $akun = $modelAkun->findAll();

            $db = \Config\Database::connect();
            $builderAkunKategori = $db->table('akun_kategori');

            $data = [
                'akun'        => $akun,
                'kategori'    => $builderAkunKategori->get()->getResultArray(),
            ];

            $json = [
                'data'          => view('Akun/listAkun/add', $data),
            ];

            echo json_encode($json);
        } else {
            return 'Tidak bisa load';
        }
    }


    public function create()
    {
        if ($this->request->isAJAX()) {

            $validasi = [
                'kode'       => [
                    'rules'  => 'required|is_unique[akun.kode]',
                    'errors' => [
                        'required'  => '{field} akun harus diisi.',
                        'is_unique' => 'Kode akun sudah ada dalam database.'
                    ]
                ],
                'nama'       => [
                    'rules'  => 'required|is_unique[akun.nama]',
                    'errors' => [
                        'required'  => '{field} akun harus diisi.',
                        'is_unique' => 'Nama akun sudah ada dalam database.'
                    ]
                ],
                'kategori'  => [
                    'rules'  => 'required',
                    'errors' => [
                        'required'  => '{field} deskripsi harus diisi.',
                    ]
                ],
            ];

            if (!$this->validate($validasi)) {
                $validation = \Config\Services::validation();

                $error = [
                    'error_kode'       => $validation->getError('kode'),
                    'error_nama'       => $validation->getError('nama'),
                    'error_deskripsi'  => $validation->getError('deskripsi')
                ];

                $json = [
                    'error' => $error
                ];
            } else {
                $modelAkun = new AkunModel();

                $data = [
                    'kode'         => $this->request->getPost('kode'),    
                    'nama'         => $this->request->getPost('nama'),
                    'id_kategori'  => $this->request->getPost('id_kategori')
                ];
                $modelAkun->insert($data);

                $json = [
                    'success' => 'Berhasil menambah data produk'
                ];
            }

            echo json_encode($json);
        } else {
            return 'Tidak bisa load';
        }
    }


    public function edit($id = null)
    {
        if ($this->request->isAJAX()) {
            
            $modelAkun = new AkunModel();
            $akun      = $modelAkun->find($id);
            $kategori  = $modelAkun->getAkun($id);

            $data = [
                'validation'    => \Config\Services::validation(),
                'akun'          => $akun,
                'kategori'      => $kategori
            ];

            $json = [
                'data'   => view('akun/listAkun/edit', $data),
            ];

            echo json_encode($json);
        } else {
            return 'Tidak bisa load data';
        }
    }


    public function delete($id = null)
    {
        $modelAkun = new AkunModel();

        $modelAkun->delete($id);

        session()->setFlashdata('pesan', 'Data berhasil dihapus.');
        return redirect()->to('/listakun');
    }
}