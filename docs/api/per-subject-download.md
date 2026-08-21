# Per-subject download API

Contract for the mobile app's offline-first subject downloads. The app renders
from its local cache and downloads one subject at a time on demand, so this
splits into two endpoints with very different cost profiles.

## Background: one subject is many `subjects` rows

`subjects` stores **one row per (name, year, region)** — `year` and `region` are
columns on `subjects`, and the unique index on `name` was dropped in
`2025_05_15_202215_remove_unique_constraint_from_subjects_name`.

So "Biology" is not a row, it is a set of rows. The app has always shown one card
per distinct *name* and grouped questions by `subject.name`. These endpoints make
that grouping server-side, so **one card tap is exactly one request**.

Consequence: the download key is the subject **name**, not a `subjects.id`.
Keying on a raw id would download one year of one region — not a unit any user
recognises.

---

## `GET /api/subjects/catalogue`

Small, called on every app launch. Never carries question or note bodies.

**Query parameters**

| Name | Required | Notes |
|---|---|---|
| `user_id` | yes | must exist in `users` |

**Response**

```json
{
  "status": "success",
  "is_subscribed": true,
  "free_question_limit": 40,
  "data": [
    {
      "key": "Biology",
      "name": "Biology",
      "subject_ids": [12, 45, 78],
      "years": ["2017", "2016", "2015"],
      "regions": ["Addis Ababa", "Oromia"],
      "duration": 2,
      "is_sample": false,
      "question_count": 420,
      "note_count": 33,
      "image_count": 86,
      "estimated_size_bytes": 3810422,
      "content_version": "2026-02-11 09:00:00"
    }
  ]
}
```

| Field | Meaning |
|---|---|
| `key` | download key — pass as `subject` to the content endpoint |
| `subject_ids` | the underlying `subjects` rows, for debugging |
| `duration` | `subjects.default_duration`, normalised to an integer or null |
| `is_sample` | true if **any** variant is flagged as a sample subject |
| `estimated_size_bytes` | **uncompressed** JSON estimate. Wire transfer is gzipped, so the real download is roughly a quarter of this. Rough by design — do not display it as exact |
| `image_count` | images downloaded separately, after the JSON |
| `content_version` | max `updated_at` across the subject's questions and notes. Changes whenever content changes |

**Errors**

- `422` — missing or unknown `user_id`
- `400` — user has no `type_id`

---

## `GET /api/subjects/content`

Large, called once per subject the user chooses to download. Returns everything
needed to make that subject fully usable offline.

**Query parameters**

| Name | Required | Notes |
|---|---|---|
| `user_id` | yes | must exist in `users` |
| `subject` | yes | the `key` from the catalogue |
| `known_version` | no | a `content_version` the client already holds |

**Response**

```json
{
  "status": "success",
  "is_subscribed": true,
  "subject": { "...the catalogue entry for this subject..." },
  "questions": [
    {
      "id": 1,
      "correct_choice_id": 4,
      "subject_id": 12,
      "year_group_id": 3,
      "chapter_id": 7,
      "question_text": "...",
      "question_image_path": "https://host/storage/questions/1.png",
      "formula": null,
      "explanation": "...",
      "explanation_image_path": null,
      "created_at": "...",
      "updated_at": "...",
      "type_id": 2,
      "duration": 2,
      "choices": [
        {
          "id": 4,
          "question_id": 1,
          "choice_text": "...",
          "choice_image_path": null,
          "formula": null,
          "created_at": "...",
          "updated_at": "..."
        }
      ],
      "subject": { "id": 12, "name": "Biology", "year": "2015", "region": "Addis Ababa", "...": "..." },
      "chapter": { "id": 7, "name": "Cell Division", "...": "..." },
      "year_group": { "id": 3, "year": 2015 }
    }
  ],
  "notes": {
    "id": "12",
    "name": "Biology",
    "chapters": [
      {
        "id": "7",
        "name": "Cell Division",
        "notes": [
          {
            "id": "88",
            "title": "Mitosis",
            "content": "...",
            "subjectId": "12",
            "subjectName": "Biology",
            "grade": 12,
            "language": "english",
            "chapterId": "7",
            "chapterName": "Cell Division",
            "createdAt": "2026-01-02T10:00:00.000000Z",
            "updatedAt": "2026-01-02T10:00:00.000000Z"
          }
        ]
      }
    ]
  }
}
```

Notes on the shape, all deliberate:

- **`questions[].subject` is the whole variant row.** The app reads `year` and
  `region` off it. Replacing it with an id breaks question parsing.
- **Image paths are absolute** (`asset('storage/...')`). The app downloads them
  directly; a bare storage path is a broken image.
- **`questions` is flat**, not grouped by year — the app builds its own year and
  chapter groupings locally.
- **`notes` is a single subject object**, matching the app's existing
  `NoteSubjectModel` shape, with camelCase keys matching `NoteModel.fromJson`.
- Questions are ordered by `id` ascending, matching the bulk endpoint.

**When `known_version` matches**

```json
{ "status": "not_modified", "subject": { "...": "..." } }
```

Status code is still `200`, with no `questions` or `notes` keys. HTTP `304` was
avoided because it interacts badly with Dio's response handling.

**Errors**

- `422` — missing/unknown `user_id`, or missing `subject`
- `400` — user has no `type_id`
- `404` — no subject with that name is available to this user

---

## Subscription behaviour

Unsubscribed users receive at most `SubjectContentService::FREE_QUESTION_LIMIT`
(40) questions per subject. This mirrors the existing cap in
`QuestionController::getQuestionsByType`, so moving the app onto these endpoints
does not change who can see what.

Two pre-existing inconsistencies were **left alone** rather than changed
silently, because both are product decisions:

1. `getQuestionsByType` checks `payment_status = paid` with no `type_id`
   predicate; `NoteController::forUserGrouped` also filters on `type_id`. The new
   endpoints follow `getQuestionsByType`.
2. Notes are currently free to everyone — the subscription gate in
   `forUserGrouped` is commented out (and has been). The new content endpoint
   returns notes on the same terms.
3. `subjects.is_sample` exists and the app treats sample subjects as the free
   ones, but no backend endpoint consults it. The 40-question cap applies
   uniformly instead. Worth deciding on.

---

## Progress reporting

All responses go through `App\Http\Traits\RespondsWithJson`, which sets an
explicit `Content-Length` and removes `Transfer-Encoding`. Without it, Dio
reports `total = -1` in `onReceiveProgress` and the app can only show an
indeterminate spinner. Any new endpoint the app consumes must use this trait.

Note the advertised length is the **gzipped** size when the client sends
`Accept-Encoding: gzip`, which Dio does by default. That is the right number for
a progress bar, but it is not comparable to `estimated_size_bytes` above.

---

## Client-side landmines

Two existing parsers in the app will crash on well-formed responses from these
endpoints. Both need fixing on the Flutter side:

- `Subject.fromJson` does `int.tryParse(json['default_duration'])`.
  `int.tryParse` requires a `String`; `default_duration` is an integer column.
  This survives today only because PDO returns MySQL integers as strings. The
  catalogue endpoint returns `duration` as a real integer, so the app must use a
  tolerant parse.
- `NoteModel.fromJson` does `int.tryParse(json['grade'] ?? '0')`, and
  `notes.grade` is an `unsignedTinyInteger`. Same failure, currently dormant only
  because grades are mostly null.

---

## Related changes in this PR

- `GET /api/questions/subject` now **requires `user_id`**, filters by the user's
  exam type, applies the subscription cap, eager-loads `subject` and `chapter`,
  and returns absolute image URLs. Previously it served a subject's full question
  set to any caller who could guess an id.
- `GET /api/notes/for-user-grouped` accepts optional `subject_id` / `subject`
  filters. Default behaviour is unchanged.
- `POST /api/subjects` (`availableSubjects`) is deprecated in favour of
  `subjects/catalogue`. Still routed and unchanged.
