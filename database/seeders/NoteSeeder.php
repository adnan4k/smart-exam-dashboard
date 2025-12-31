<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Note;
use App\Models\User;
use App\Models\Type;
use App\Models\Subject;
use App\Models\Chapter;
use Illuminate\Support\Facades\DB;

class NoteSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Get existing data
        $users = User::all();
        $types = Type::all();
        $subjects = Subject::all();
        $chapters = Chapter::all();

        // If no data exists, create some basic data first
        if ($subjects->isEmpty()) {
            $this->command->warn('No subjects found. Please run SubjectSeeder first.');
            return;
        }

        // Create sample chapters if none exist
        if ($chapters->isEmpty()) {
            $this->command->info('No chapters found. Creating sample chapters...');
            
            $chapterNames = [
                'Introduction',
                'Fundamentals',
                'Advanced Concepts',
                'Applications',
                'Review and Practice',
            ];
            
            // Create generic chapters (chapters are independent of subjects)
            foreach ($chapterNames as $index => $chapterName) {
                $chapterNumber = $index + 1;
                Chapter::create([
                    'name' => $chapterName,
                    'description' => "Chapter {$chapterNumber}: {$chapterName} - General study material",
                ]);
            }
            
            // Refresh chapters collection
            $chapters = Chapter::all();
            $this->command->info('Created ' . $chapters->count() . ' sample chapters.');
        }

        if ($types->isEmpty()) {
            $this->command->warn('No types found. Please run TypeSeeder first.');
            return;
        }

        // Sample note titles and content
        $sampleNotes = [
            [
                'title' => 'Introduction to Algebra',
                'content' => 'Algebra is a branch of mathematics that uses symbols and letters to represent numbers and quantities in formulas and equations. It helps us solve problems by finding unknown values.',
                'grade' => 7,
                'language' => 'english',
            ],
            [
                'title' => 'Basic Geometry Concepts',
                'content' => 'Geometry is the study of shapes, sizes, and properties of space. Key concepts include points, lines, angles, triangles, circles, and polygons. Understanding these fundamentals is crucial for advanced mathematics.',
                'grade' => 8,
                'language' => 'english',
            ],
            [
                'title' => 'Chemical Reactions',
                'content' => 'Chemical reactions occur when substances interact to form new products. The law of conservation of mass states that matter cannot be created or destroyed, only transformed.',
                'grade' => 9,
                'language' => 'english',
            ],
            [
                'title' => 'World War II Overview',
                'content' => 'World War II (1939-1945) was a global conflict involving most of the world\'s nations. It resulted in significant political, social, and economic changes worldwide.',
                'grade' => 10,
                'language' => 'english',
            ],
            [
                'title' => 'Literary Analysis Techniques',
                'content' => 'Literary analysis involves examining a text\'s structure, themes, characters, and literary devices. Key techniques include close reading, identifying symbolism, and understanding narrative perspective.',
                'grade' => 11,
                'language' => 'english',
            ],
            [
                'title' => 'Photosynthesis Process',
                'content' => 'Photosynthesis is the process by which plants convert light energy into chemical energy. The equation is: 6CO₂ + 6H₂O + light energy → C₆H₁₂O₆ + 6O₂.',
                'grade' => 10,
                'language' => 'english',
            ],
            [
                'title' => 'Calculus Fundamentals',
                'content' => 'Calculus is the mathematical study of continuous change. It has two main branches: differential calculus (rates of change) and integral calculus (accumulation of quantities).',
                'grade' => 12,
                'language' => 'english',
            ],
            [
                'title' => 'Introduction to Physics',
                'content' => 'Physics is the natural science that studies matter, motion, and behavior through space and time. Key concepts include force, energy, momentum, and the laws of motion.',
                'grade' => 9,
                'language' => 'english',
            ],
        ];

        // Create notes for each subject-chapter combination
        // Since chapters are independent, we'll create notes by pairing subjects with chapters
        $noteCount = 0;
        foreach ($subjects as $subject) {
            // Use all available chapters (chapters are independent of subjects)
            foreach ($chapters as $chapter) {
                // Create 2-3 notes per chapter-subject combination
                $notesToCreate = min(3, count($sampleNotes));
                
                for ($i = 0; $i < $notesToCreate; $i++) {
                    $sampleNote = $sampleNotes[($noteCount % count($sampleNotes))];
                    
                    Note::create([
                        'user_id' => $users->isNotEmpty() ? $users->random()->id : null,
                        'type_id' => $types->isNotEmpty() ? $types->random()->id : null,
                        'subject_id' => $subject->id,
                        'chapter_id' => $chapter->id,
                        'title' => $sampleNote['title'] . ' - ' . $chapter->name,
                        'content' => $sampleNote['content'],
                        'grade' => $sampleNote['grade'],
                        'language' => $sampleNote['language'],
                    ]);
                    
                    $noteCount++;
                }
            }
        }

        // Create some additional notes with different languages
        $languages = ['amharic', 'afan_oromo', 'english', 'tigrinya', 'somali', 'afar'];
        
        if ($subjects->isNotEmpty() && $chapters->isNotEmpty()) {
            $randomSubject = $subjects->random();
            $randomChapter = $chapters->random();
            
            foreach (['amharic', 'afan_oromo', 'tigrinya'] as $lang) {
                Note::create([
                    'user_id' => $users->isNotEmpty() ? $users->random()->id : null,
                    'type_id' => $types->isNotEmpty() ? $types->random()->id : null,
                    'subject_id' => $randomSubject->id,
                    'chapter_id' => $randomChapter->id,
                    'title' => 'Sample Note in ' . ucfirst($lang),
                    'content' => 'This is a sample note written in ' . $lang . '. This demonstrates multilingual support in the notes system.',
                    'grade' => rand(7, 12),
                    'language' => $lang,
                ]);
            }
        }

        $this->command->info('Notes seeded successfully! Created ' . $noteCount . ' notes.');
    }
}

