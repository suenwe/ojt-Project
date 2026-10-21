<?php

namespace Database\Seeders;

use App\Models\Keyword;
use Illuminate\Database\Seeder;

class KeywordSeeder extends Seeder
{
    public function run(): void
    {
        $keywords = [
            'Fuck',
            'ကုလား',
            'ဘင်္ဂါလီ',
            'သာသနာဖျက်',
            'စုန်းမ',
            'စောက်',
            'ရိုက်',
            'သတ်',
            'မီးရှို့',
            'Kill Yourself',
            'Die',
            'Suicide',
        ];

        foreach ($keywords as $keyword) {
            Keyword::query()->firstOrCreate([
                'keyword_text' => $keyword,
            ]);
        }
    }
}
