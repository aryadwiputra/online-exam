<?php

use App\Models\Classroom;
use App\Models\Exam;
use App\Models\Student;
use App\Models\User;
use Illuminate\Http\UploadedFile;

beforeEach(function () {
    $this->admin = User::factory()->create();
});

it('imports students from a csv file', function () {
    Classroom::factory()->create(['title' => 'Kelas 10A']);

    $csv = <<<'CSV'
nisn,name,classroom,gender,password
0011111111,Andi Wijaya,Kelas 10A,L,rahasia123
0011111112,Dewi Lestari,Kelas 10A,P,rahasia123
CSV;

    $this->actingAs($this->admin)
        ->post('/admin/students/import', [
            'file' => UploadedFile::fake()->createWithContent('siswa.csv', $csv),
        ])
        ->assertRedirect(route('admin.students.index'))
        ->assertSessionHasNoErrors();

    expect(Student::count())->toBe(2)
        ->and(Student::where('nisn', '0011111112')->first()->name)->toBe('Dewi Lestari');
});

it('reports invalid student rows without stopping the import', function () {
    Classroom::factory()->create(['title' => 'Kelas 10A']);

    $csv = <<<'CSV'
nisn,name,classroom,gender,password
0011111111,Andi Wijaya,Kelas 10A,L,rahasia123
0011111112,Dewi Lestari,Kelas Tidak Ada,P,rahasia123
CSV;

    $response = $this->actingAs($this->admin)
        ->post('/admin/students/import', [
            'file' => UploadedFile::fake()->createWithContent('siswa.csv', $csv),
        ]);

    expect(Student::count())->toBe(1)
        ->and($response->getSession()->get('import_errors'))->toHaveCount(1);
});

it('rejects non csv uploads', function () {
    $this->actingAs($this->admin)
        ->post('/admin/students/import', [
            'file' => UploadedFile::fake()->create('siswa.pdf', 100, 'application/pdf'),
        ])
        ->assertSessionHasErrors('file');
});

it('downloads the student import template', function () {
    $this->actingAs($this->admin)
        ->get('/admin/students/import/template')
        ->assertOk()
        ->assertHeader('content-type', 'text/csv; charset=UTF-8');
});

it('imports questions with one based answer numbers', function () {
    $exam = Exam::factory()->create();

    $csv = <<<'CSV'
question,option_1,option_2,option_3,option_4,option_5,answer
Ibu kota Indonesia adalah?,Jakarta,Bandung,Surabaya,Medan,,1
Hasil 3 x 3 adalah?,6,9,12,,,2
CSV;

    $this->actingAs($this->admin)
        ->post("/admin/exams/{$exam->id}/questions/import", [
            'file' => UploadedFile::fake()->createWithContent('soal.csv', $csv),
        ])
        ->assertRedirect(route('admin.exams.show', $exam));

    $questions = $exam->questions()->orderBy('id')->get();

    expect($questions)->toHaveCount(2)
        ->and($questions[0]->options)->toBe(['Jakarta', 'Bandung', 'Surabaya', 'Medan'])
        ->and($questions[0]->answer_index)->toBe(0)
        ->and($questions[1]->options)->toBe(['6', '9', '12'])
        ->and($questions[1]->answer_index)->toBe(1);
});

it('reports rows whose answer points to an empty option', function () {
    $exam = Exam::factory()->create();

    $csv = <<<'CSV'
question,option_1,option_2,option_3,option_4,option_5,answer
Soal tidak valid,A,B,,,,"4"
CSV;

    $response = $this->actingAs($this->admin)
        ->post("/admin/exams/{$exam->id}/questions/import", [
            'file' => UploadedFile::fake()->createWithContent('soal.csv', $csv),
        ]);

    expect($exam->questions()->count())->toBe(0)
        ->and($response->getSession()->get('import_errors'))->toHaveCount(1);
});

it('requires authentication to import', function () {
    $exam = Exam::factory()->create();

    $this->post("/admin/exams/{$exam->id}/questions/import", [
        'file' => UploadedFile::fake()->createWithContent('soal.csv', "question,answer\nA,1\n"),
    ])->assertRedirect(route('login'));
});
