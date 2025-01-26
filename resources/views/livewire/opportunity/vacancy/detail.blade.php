<div class="flex justify-center px-6 py-8 bg-gray-50 min-h-screen">
    <div class="w-full max-w-4xl bg-white shadow-lg rounded-lg p-8 dark:bg-gray-800">
        <!-- Vacancy Title -->
        <div class="mb-6 text-center">
            <h1 class="text-4xl font-bold text-gray-900 dark:text-gray-100">{{ $vacancy->title }}</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-2">Posted on: {{ $vacancy->created_at->format('M d, Y') }}</p>
        </div>

        <!-- Description -->
        <div class="mb-8 border-b pb-6 border-gray-200 dark:border-gray-700">
            <h2 class="text-2xl font-semibold text-gray-800 dark:text-gray-100">Description</h2>
            <p class="text-gray-600 dark:text-gray-300 mt-4">{{ $vacancy->description }}</p>
        </div>

        <!-- Job Details -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
            <div>
                <h3 class="text-lg font-medium text-gray-700 dark:text-gray-300">Experience</h3>
                <p class="text-gray-600 dark:text-gray-400 mt-1">{{ $vacancy->experience }}</p>
            </div>
            <div>
                <h3 class="text-lg font-medium text-gray-700 dark:text-gray-300">Job Type</h3>
                <p class="text-gray-600 dark:text-gray-400 mt-1">{{ $vacancy->jobType }}</p>
            </div>
            <div>
                <h3 class="text-lg font-medium text-gray-700 dark:text-gray-300">Location</h3>
                <p class="text-gray-600 dark:text-gray-400 mt-1">{{ $vacancy->location }}</p>
            </div>
            <div>
                <h3 class="text-lg font-medium text-gray-700 dark:text-gray-300">Languages</h3>
                <p class="text-gray-600 dark:text-gray-400 mt-1">{{ $vacancy->languages }}</p>
            </div>
            <div>
                <h3 class="text-lg font-medium text-gray-700 dark:text-gray-300">Start Date</h3>
                <p class="text-gray-600 dark:text-gray-400 mt-1">{{ $vacancy->startDate->format('M d, Y') }}</p>
            </div>
            <div>
                <h3 class="text-lg font-medium text-gray-700 dark:text-gray-300">Deadline</h3>
                <p class="text-gray-600 dark:text-gray-400 mt-1">{{ $vacancy->deadline->format('M d, Y') }}</p>
            </div>
        </div>

        <!-- Key Responsibilities -->
        <div class="mb-8">
            <h2 class="text-2xl font-semibold text-gray-800 dark:text-gray-100">Key Responsibilities</h2>
            <ul class="list-disc list-inside text-gray-600 dark:text-gray-300 mt-4 space-y-2">
                @foreach (explode("\n", $vacancy->keyResponsibilities) as $responsibility)
                    <li>{{ $responsibility }}</li>
                @endforeach
            </ul>
        </div>

        <!-- Qualifications -->
        <div class="mb-8">
            <h2 class="text-2xl font-semibold text-gray-800 dark:text-gray-100">Qualifications</h2>
            <p class="text-gray-600 dark:text-gray-300 mt-4">{{ $vacancy->qualifications }}</p>
        </div>

        <!-- Contact Information -->
        <div class="mb-8 grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <h3 class="text-lg font-medium text-gray-700 dark:text-gray-300">Contact Email</h3>
                <p class="text-gray-600 dark:text-gray-400 mt-1">{{ $vacancy->email }}</p>
            </div>
            <div>
                <h3 class="text-lg font-medium text-gray-700 dark:text-gray-300">Phone Number</h3>
                <p class="text-gray-600 dark:text-gray-400 mt-1">{{ $vacancy->phone }}</p>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="mt-10 flex justify-between items-center">
            <button
                class="bg-blue-600 text-white px-6 py-2 rounded-lg shadow hover:bg-blue-700 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-blue-400">
                Apply Now
            </button>
            <button
                class="bg-gray-100 text-gray-800 px-6 py-2 rounded-lg shadow hover:bg-gray-200 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-gray-300 dark:bg-gray-700 dark:text-gray-200 dark:hover:bg-gray-600">
                Back to Listings
            </button>
        </div>
    </div>
</div>
