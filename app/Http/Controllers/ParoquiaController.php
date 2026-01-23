<?php

namespace App\Http\Controllers;

use App\Models\SisMatrizParoquia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class ParoquiaController extends Controller
{
    public function index(Request $request)
    {
        $query = SisMatrizParoquia::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('city', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $paroquias = $query->orderBy('added_at', 'desc')->paginate(10);

        return view('paroquias.index', compact('paroquias'));
    }

    public function create()
    {
        return view('paroquias.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:255',
            'diocese' => 'nullable|string|max:255',
            'region' => 'nullable|integer',
            'paroco' => 'nullable|string|max:255',
            'status' => 'required|in:0,1',
            'foto' => 'nullable|image|max:2048',
        ]);

        $data = $request->except('foto');
        $data['added_at'] = now();

        if ($request->hasFile('foto')) {
            $file = $request->file('foto');
            // Using a unique ID to avoid filename conflicts
            $filename = 'paroquia_' . uniqid() . '.' . $file->getClientOriginalExtension();
            
            try {
                // Try to upload to SFTP first if configured
                if (config('filesystems.disks.sftp_public.host')) {
                    $path = 'paroquias/' . $filename;

                    // Ensure directory exists
                    if (!Storage::disk('sftp_public')->exists('paroquias')) {
                        Storage::disk('sftp_public')->makeDirectory('paroquias');
                    }

                    $uploaded = Storage::disk('sftp_public')->put($path, file_get_contents($file));
                    
                    if ($uploaded) {
                        $data['foto'] = $filename;
                    } else {
                        throw new \Exception('Upload returned false without throwing exception.');
                    }
                } else {
                    throw new \Exception('SFTP not configured');
                }
            } catch (\Exception $e) {
                // Ensure the upload directory exists
                if (!file_exists(public_path('uploads/paroquias'))) {
                    mkdir(public_path('uploads/paroquias'), 0755, true);
                }

                // Move the file to the public/uploads/paroquias directory
                // Note: For production with separate backend, ensure this directory is mapped correctly
                $file->move(public_path('uploads/paroquias'), $filename);
                $data['foto'] = $filename;
            }
        }

        SisMatrizParoquia::create($data);

        return redirect()->route('paroquias.index')->with('success', 'Paróquia cadastrada com sucesso!');
    }

    public function edit($id)
    {
        $paroquia = SisMatrizParoquia::findOrFail($id);
        return view('paroquias.edit', compact('paroquia'));
    }

    public function update(Request $request, $id)
    {
        $paroquia = SisMatrizParoquia::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:255',
            'diocese' => 'nullable|string|max:255',
            'region' => 'nullable|integer',
            'paroco' => 'nullable|string|max:255',
            'status' => 'required|in:0,1',
            'foto' => 'nullable|image|max:2048',
        ]);

        $data = $request->except('foto');

        if ($request->hasFile('foto')) {
            // Delete old photo if exists
            if ($paroquia->foto) {
                // Try to delete from SFTP
                if (config('filesystems.disks.sftp_public.host')) {
                    try {
                        Storage::disk('sftp_public')->delete('paroquias/' . $paroquia->foto);
                    } catch (\Exception $e) {
                        \Log::error('SFTP Delete failed: ' . $e->getMessage());
                    }
                }
                // Try to delete from local
                if (file_exists(public_path('uploads/paroquias/' . $paroquia->foto))) {
                    unlink(public_path('uploads/paroquias/' . $paroquia->foto));
                }
            }

            $file = $request->file('foto');
            $filename = 'paroquia_' . uniqid() . '.' . $file->getClientOriginalExtension();
            
            try {
                // Try to upload to SFTP first if configured
                if (config('filesystems.disks.sftp_public.host')) {
                    $path = 'paroquias/' . $filename;

                    // Ensure directory exists
                    if (!Storage::disk('sftp_public')->exists('paroquias')) {
                        Storage::disk('sftp_public')->makeDirectory('paroquias');
                    }

                    $uploaded = Storage::disk('sftp_public')->put($path, file_get_contents($file));
                    
                    if ($uploaded) {
                        $data['foto'] = $filename;
                    } else {
                        throw new \Exception('Upload returned false without throwing exception.');
                    }
                } else {
                    throw new \Exception('SFTP not configured');
                }
            } catch (\Exception $e) {
                // Ensure the upload directory exists
                if (!file_exists(public_path('uploads/paroquias'))) {
                    mkdir(public_path('uploads/paroquias'), 0755, true);
                }

                $file->move(public_path('uploads/paroquias'), $filename);
                $data['foto'] = $filename;
            }
        }

        $paroquia->update($data);

        return redirect()->route('paroquias.index')->with('success', 'Paróquia atualizada com sucesso!');
    }

    public function destroy(Request $request, $id)
    {
        $request->validate([
            'password' => 'required|string',
        ]);

        if (!Hash::check($request->password, $request->user()->senha)) {
            return back()->with('error', 'Senha de administrador incorreta.');
        }

        $paroquia = SisMatrizParoquia::findOrFail($id);
        
        // Delete photo if exists
        if ($paroquia->foto) {
            // Try to delete from SFTP
            if (config('filesystems.disks.sftp_public.host')) {
                try {
                    Storage::disk('sftp_public')->delete('paroquias/' . $paroquia->foto);
                } catch (\Exception $e) {
                    \Log::error('SFTP Delete failed: ' . $e->getMessage());
                }
            }
            // Try to delete from local
            if (file_exists(public_path('uploads/paroquias/' . $paroquia->foto))) {
                unlink(public_path('uploads/paroquias/' . $paroquia->foto));
            }
        }

        $paroquia->delete();

        return redirect()->route('paroquias.index')->with('success', 'Paróquia excluída com sucesso!');
    }
}
