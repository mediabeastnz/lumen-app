<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://unpkg.com/tailwindcss@^2/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://unpkg.com/@tailwindcss/forms/dist/forms.min.css" rel="stylesheet">
    <title>PrimeX Import</title>
</head>

<body>
    <div class="min-h-screen bg-gray-50 flex flex-col justify-center py-12 sm:px-6 lg:px-8">
        <div class="sm:mx-auto sm:w-full sm:max-w-lg">
            <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900">
                Import your data
            </h2>
            <p class="mt-6 text-center text-base font-normal text-indigo-600 hover:text-indigo-500">
                Products Format: <span class="text-sm uppercase text-indigo-500">product_id, name, description</span><br />
                Stocks Format: <span class="text-sm uppercase text-indigo-500">product_id, on_hand, taken, production_date</span><br />
                <span class="block mt-6 text-sm text-red-500">First row in this demo is being automatically removed</span>
            </p>
        </div>

        <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-md">
            <div class="bg-white py-8 px-4 shadow sm:rounded-lg sm:px-10">
                <form class="space-y-6" method="POST" action="/import" enctype="multipart/form-data">

                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700">
                            Type
                        </label>
                        <div class="mt-1">
                            <select id="type" name="type" required
                                class="w-full block focus:ring-indigo-500 focus:border-indigo-500 w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                                <option value="products">Products - (Create + Update)</option>
                                <option value="stocks">Stocks - (Create)</option>
                            </select>
                        </div>
                    </div>

                    <div class="sm:col-span-6">
                        <label for="file" class="block text-sm font-medium text-gray-700">
                            CSV
                        </label>
                        <div
                            class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-md">
                            <div class="space-y-1 text-center">
                                <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none"
                                    viewBox="0 0 48 48" aria-hidden="true">
                                    <path
                                        d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02"
                                        stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                                <div class="flex text-sm text-gray-600">
                                    <label for="file"
                                        class="relative cursor-pointer bg-white rounded-md font-medium text-indigo-600 hover:text-indigo-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-indigo-500">
                                        <span>Upload a file</span>
                                        <input id="file" name="file" type="file" class="sr-only" accept=".csv">
                                    </label>
                                    <p class="pl-1">or drag and drop</p>
                                </div>
                                <p class="text-xs text-gray-500">
                                    CSV files only
                                </p>
                            </div>
                        </div>
                    </div>

                    <div>
                        <button type="submit"
                            class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            Import
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>

</html>
