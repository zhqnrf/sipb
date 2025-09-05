<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Hash;

class RolePasswordStore
{
    const PATH = 'sipb_auth.json'; // storage/app/sipb_auth.json

    protected function defaultData()
    {
        // default awal (admin123 / kepsek123)
        return [
            'admin'  => Hash::make('admin123'),
            'kepsek' => Hash::make('kepsek123'),
            'updated_at' => now()->toDateTimeString(),
        ];
    }

    public function read(): array
    {
        if (!Storage::exists(self::PATH)) {
            $data = $this->defaultData();
            Storage::put(self::PATH, json_encode($data, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE));
            return $data;
        }
        $raw = Storage::get(self::PATH);
        $json = json_decode($raw, true);
        if (!is_array($json) || !isset($json['admin']) || !isset($json['kepsek'])) {
            $json = $this->defaultData();
            Storage::put(self::PATH, json_encode($json, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE));
        }
        return $json;
    }

    public function set(string $role, string $newPlain): void
    {
        $data = $this->read();
        if (!in_array($role, ['admin','kepsek'], true)) {
            throw new \InvalidArgumentException('Role tidak valid');
        }
        $data[$role] = Hash::make($newPlain);
        $data['updated_at'] = now()->toDateTimeString();
        Storage::put(self::PATH, json_encode($data, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE));
    }

    public function check(string $role, string $plain): bool
    {
        $data = $this->read();
        if (!isset($data[$role])) return false;
        return Hash::check($plain, $data[$role]);
    }
}
