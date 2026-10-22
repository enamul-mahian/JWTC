<?php

namespace App\Http\Controllers\API\Jobseeker;

use App\Enum\UserType;
use App\Http\Controllers\Controller;
use App\Models\Education;
use App\Models\JobseekerCertificate;
use App\Models\JobseekerSkill;
use App\Models\JobseekerUploads;
use App\Models\Language;
use App\Models\Training;
use App\Models\UploadedCv;
use App\Models\WorkExperience;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class OthersController extends Controller
{
    private function applicant(Request $request): void
    {
        abort_unless($request->user() && $request->user()->user_type === UserType::JOB_SEEKER,
            403, 'Job seeker account required.');
    }

    private function save(Request $request, string $model, array $rules, ?int $id = null)
    {
        $this->applicant($request);
        $validated = $request->validate($rules);
        if ($id !== null) {
            $row = $model::where('user_id', $request->user()->id)->findOrFail($id);
            $row->update($validated);
            return response()->json(['message' => 'Updated.', 'data' => $row]);
        }

        $row = $model::create($validated + ['user_id' => $request->user()->id]);
        return response()->json(['message' => 'Created.', 'data' => $row], 201);
    }

    private function remove(Request $request, string $model, int $id)
    {
        $this->applicant($request);
        $row = $model::where('user_id', $request->user()->id)->findOrFail($id);
        $row->delete();
        return response()->json(['message' => 'Deleted.']);
    }

    private function rules(array $fields, bool $create = false): array
    {
        $rules = [];
        foreach ($fields as $name => $type) {
            $rules[$name] = array_merge([$create ? 'required' : 'sometimes', 'nullable'], explode('|', $type));
        }
        return $rules;
    }

    public function storeLanguage(Request $r)
    {
        return $this->save($r, Language::class, $this->rules([
            'language_name' => 'string|max:255',
            'listening_proficiency' => 'string|max:100',
            'reading_proficiency' => 'string|max:100',
            'writting_proficiency' => 'string|max:100',
        ]));
    }

    public function updateLanguage(Request $r, int $id)
    {
        return $this->save($r, Language::class, $this->rules([
            'language_name' => 'string|max:255',
            'listening_proficiency' => 'string|max:100',
            'reading_proficiency' => 'string|max:100',
            'writting_proficiency' => 'string|max:100',
        ]), $id);
    }

    public function deleteLanguage(Request $r, int $id)
    {
        return $this->remove($r, Language::class, $id);
    }

    public function storeSkill(Request $r)
    {
        return $this->save($r, JobseekerSkill::class, $this->rules([
            'skill_name' => 'string|max:255',
            'skill_proficiency' => 'string|max:100',
        ]));
    }

    public function updateSkill(Request $r, int $id)
    {
        return $this->save($r, JobseekerSkill::class, $this->rules([
            'skill_name' => 'string|max:255',
            'skill_proficiency' => 'string|max:100',
        ]), $id);
    }

    public function deleteSkill(Request $r, int $id)
    {
        return $this->remove($r, JobseekerSkill::class, $id);
    }

    public function storeEducation(Request $r)
    {
        return $this->save($r, Education::class, $this->rules([
            'degree_level_id' => 'integer|min:1',
            'degree_name_id' => 'integer|min:1',
            'institute_name' => 'string|max:255',
            'result' => 'string|max:255',
            'pass_year' => 'integer|between:1950,2100',
        ]));
    }

    public function updateEducation(Request $r, int $id)
    {
        return $this->save($r, Education::class, $this->rules([
            'degree_level_id' => 'integer|min:1',
            'degree_name_id' => 'integer|min:1',
            'institute_name' => 'string|max:255',
            'result' => 'string|max:255',
            'pass_year' => 'integer|between:1950,2100',
        ]), $id);
    }

    public function deleteEducation(Request $r, int $id)
    {
        return $this->remove($r, Education::class, $id);
    }

    public function storeTraining(Request $r)
    {
        return $this->save($r, Training::class, $this->rules([
            'title' => 'string|max:255',
            'country' => 'string|max:255',
            'topic' => 'string|max:255',
            'training_year' => 'string|max:40',
            'training_institute' => 'string|max:255',
            'training_duration' => 'string|max:100',
        ]));
    }

    public function updateTraining(Request $r, int $id)
    {
        return $this->save($r, Training::class, $this->rules([
            'title' => 'string|max:255',
            'country' => 'string|max:255',
            'topic' => 'string|max:255',
            'training_year' => 'string|max:40',
            'training_institute' => 'string|max:255',
            'training_duration' => 'string|max:100',
        ]), $id);
    }

    public function deleteTraining(Request $r, int $id)
    {
        return $this->remove($r, Training::class, $id);
    }

    public function storeWorkExperience(Request $r)
    {
        return $this->save($r, WorkExperience::class, $this->rules([
            'position_name' => 'string|max:255',
            'occupation_description' => 'string|max:255',
            'employeer' => 'string|max:255',
            'city' => 'string|max:255',
            'occupation_country' => 'string|max:255',
            'from_date' => 'string|max:50',
            'to_date' => 'string|max:50',
            'continuing' => 'string|max:50',
            'responsibilities' => 'string|max:255',
        ]));
    }

    public function updateWorkExperience(Request $r, int $id)
    {
        return $this->save($r, WorkExperience::class, $this->rules([
            'position_name' => 'string|max:255',
            'occupation_description' => 'string|max:255',
            'employeer' => 'string|max:255',
            'city' => 'string|max:255',
            'occupation_country' => 'string|max:255',
            'from_date' => 'string|max:50',
            'to_date' => 'string|max:50',
            'continuing' => 'string|max:50',
            'responsibilities' => 'string|max:255',
        ]), $id);
    }

    public function deleteWorkExperience(Request $r, int $id)
    {
        return $this->remove($r, WorkExperience::class, $id);
    }

    public function getCVList(Request $r)
    {
        $this->applicant($r);
        return response()->json(UploadedCv::where('user_id', $r->user()->id)
            ->latest('id')->get(['id', 'title', 'created_at']));
    }

    public function getCertificateList(Request $r)
    {
        $this->applicant($r);
        return response()->json(JobseekerCertificate::where('user_id', $r->user()->id)
            ->latest('id')->get(['id', 'title', 'created_at']));
    }

    public function getDocumentsList(Request $r)
    {
        $this->applicant($r);
        return response()->json(JobseekerUploads::where('user_id', $r->user()->id)
            ->latest('id')->get(['id', 'title', 'created_at']));
    }

    private function download(Request $r, string $model, int $id, string $legacyFolder)
    {
        $this->applicant($r);
        $document = $model::where('user_id', $r->user()->id)->findOrFail($id);
        $stored = (string) $document->file;

        // All new API uploads are in private storage, scoped to the current user.
        $prefix = 'private/applicants/' . $r->user()->id . '/';
        if (Str::startsWith($stored, $prefix)) {
            abort_unless(Storage::disk('local')->exists($stored), 404);
            return Storage::disk('local')->download($stored, basename($document->title));
        }

        // Legacy records contain only a basename in the old public assets folder.
        // A strict basename check prevents arbitrary filesystem path traversal.
        abort_unless($stored !== '' && basename($stored) === $stored, 404);
        $root = realpath(public_path('assets/images/' . $legacyFolder));
        $file = $root ? realpath($root . DIRECTORY_SEPARATOR . $stored) : false;
        abort_unless($root && $file &&
            Str::startsWith($file, $root . DIRECTORY_SEPARATOR) &&
            is_file($file), 404);

        return response()->download($file, basename($document->title));
    }

    public function downloadCv(Request $r, int $id)
    {
        return $this->download($r, UploadedCv::class, $id, 'resume');
    }

    public function downloadCertificate(Request $r, int $id)
    {
        return $this->download($r, JobseekerCertificate::class, $id, 'certificate');
    }

    public function downloadDocuments(Request $r, int $id)
    {
        return $this->download($r, JobseekerUploads::class, $id, 'document');
    }

    public function deleteCv(Request $r, int $id)
    {
        return $this->remove($r, UploadedCv::class, $id);
    }

    public function deleteCertificate(Request $r, int $id)
    {
        return $this->remove($r, JobseekerCertificate::class, $id);
    }

    public function deleteDocuments(Request $r, int $id)
    {
        return $this->remove($r, JobseekerUploads::class, $id);
    }
}
