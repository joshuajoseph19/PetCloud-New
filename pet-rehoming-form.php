<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pet Rehoming Form - PetCloud</title>

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Custom Styles -->
    <link rel="stylesheet" href="css/breed-selector.css">

    <!-- jQuery CDN -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 2rem 1rem;
        }

        .container {
            max-width: 800px;
            margin: 0 auto;
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            padding: 2.5rem;
        }

        .form-header {
            text-align: center;
            margin-bottom: 2rem;
        }

        .form-header h1 {
            font-size: 2rem;
            color: #2d3748;
            margin-bottom: 0.5rem;
        }

        .form-header p {
            color: #718096;
            font-size: 1rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group label {
            display: block;
            font-size: 0.95rem;
            font-weight: 600;
            color: #2d3748;
            margin-bottom: 0.5rem;
        }

        .required {
            color: #e53e3e;
        }

        .form-group input[type="text"],
        .form-group input[type="number"],
        .form-group input[type="email"],
        .form-group input[type="tel"],
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 0.75rem;
            font-size: 1rem;
            border: 2px solid #e2e8f0;
            border-radius: 8px;
            background-color: #fff;
            color: #2d3748;
            transition: all 0.3s ease;
        }

        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #4299e1;
            box-shadow: 0 0 0 3px rgba(66, 153, 225, 0.1);
        }

        .form-group textarea {
            min-height: 100px;
            resize: vertical;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
        }

        .checkbox-group {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-bottom: 1rem;
        }

        .checkbox-group input[type="checkbox"] {
            width: 1.25rem;
            height: 1.25rem;
            cursor: pointer;
        }

        .checkbox-group label {
            margin: 0;
            cursor: pointer;
        }

        .submit-btn {
            width: 100%;
            padding: 1rem;
            font-size: 1.1rem;
            font-weight: 600;
            color: #fff;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 1.5rem;
        }

        .submit-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(102, 126, 234, 0.4);
        }

        .submit-btn:active {
            transform: translateY(0);
        }

        .alert {
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
            display: none;
        }

        .alert.success {
            background-color: #c6f6d5;
            color: #22543d;
            border: 1px solid #9ae6b4;
        }

        .alert.error {
            background-color: #fed7d7;
            color: #742a2a;
            border: 1px solid #fc8181;
        }

        @media (max-width: 768px) {
            .container {
                padding: 1.5rem;
            }

            .form-row {
                grid-template-columns: 1fr;
            }

            .form-header h1 {
                font-size: 1.5rem;
            }
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="form-header">
            <h1><i class="fas fa-heart"></i> Pet Rehoming Form</h1>
            <p>Help your pet find a loving new home</p>
        </div>

        <div id="alert-container" class="alert"></div>

        <form id="rehoming-form" enctype="multipart/form-data">
            <!-- Pet Type Selection -->
            <div class="form-group">
                <label for="pet-type-select">Pet Type <span class="required">*</span></label>
                <select id="pet-type-select" name="pet_type_id" required>
                    <option value="">-- Select Pet Type --</option>
                </select>
            </div>

            <!-- Breed Selector (Native Dropdown) -->
            <div class="form-group">
                <label for="breed-select">Breed <span
                        style="font-weight:normal; color:#718096;">(Optional)</span></label>
                <select id="breed-select" name="breed_id" disabled>
                    <option value="">-- Select Pet Type First --</option>
                </select>
                <div id="breed-loading" style="display:none; font-size:0.85rem; color:#666; margin-top:5px;">
                    <i class="fas fa-spinner fa-spin"></i> Loading breeds...
                </div>
            </div>

            <!-- Pet Details -->
            <div class="form-group">
                <label for="pet-name">Pet Name <span class="required">*</span></label>
                <input type="text" id="pet-name" name="pet_name" required placeholder="Enter pet's name">
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="age-years">Age (Years)</label>
                    <input type="number" id="age-years" name="age_years" min="0" max="30" placeholder="0">
                </div>
                <div class="form-group">
                    <label for="age-months">Age (Months)</label>
                    <input type="number" id="age-months" name="age_months" min="0" max="11" placeholder="0">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="gender">Gender <span class="required">*</span></label>
                    <select id="gender" name="gender" required>
                        <option value="">-- Select Gender --</option>
                        <option value="Male">Male</option>
                        <option value="Female">Female</option>
                        <option value="Unknown">Unknown</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="size">Size</label>
                    <select id="size" name="size">
                        <option value="">-- Select Size --</option>
                        <option value="Small">Small</option>
                        <option value="Medium">Medium</option>
                        <option value="Large">Large</option>
                        <option value="Extra Large">Extra Large</option>
                    </select>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="weight-kg">Weight (kg)</label>
                    <input type="number" id="weight-kg" name="weight_kg" min="0" step="0.1" placeholder="e.g., 5.5">
                </div>
                <div class="form-group">
                    <label for="color">Color</label>
                    <input type="text" id="color" name="color" placeholder="e.g., Brown, White, Black">
                </div>
            </div>

            <!-- Health Information -->
            <div class="form-group">
                <label>Health Status</label>
                <div class="checkbox-group">
                    <input type="checkbox" id="is-vaccinated" name="is_vaccinated">
                    <label for="is-vaccinated">Vaccinated</label>
                </div>
                <div class="checkbox-group">
                    <input type="checkbox" id="is-neutered" name="is_neutered">
                    <label for="is-neutered">Neutered/Spayed</label>
                </div>
            </div>

            <div class="form-group">
                <label for="health-status">Health Details</label>
                <textarea id="health-status" name="health_status"
                    placeholder="Any health conditions or medical history"></textarea>
            </div>

            <div class="form-group">
                <label for="temperament">Temperament</label>
                <textarea id="temperament" name="temperament"
                    placeholder="Describe your pet's personality and behavior"></textarea>
            </div>

            <!-- Rehoming Information -->
            <div class="form-group">
                <label for="reason">Reason for Rehoming <span class="required">*</span></label>
                <textarea id="reason" name="reason_for_rehoming" required
                    placeholder="Please explain why you need to rehome your pet"></textarea>
            </div>

            <div class="form-group">
                <label for="adoption-fee">Adoption Fee (₹)</label>
                <input type="number" id="adoption-fee" name="adoption_fee" min="0" step="0.01" placeholder="0.00">
            </div>

            <!-- Location -->
            <div class="form-group">
                <label for="location">Full Address <span class="required">*</span></label>
                <input type="text" id="location" name="location" required placeholder="Street address">
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="city">City <span class="required">*</span></label>
                    <input type="text" id="city" name="city" required placeholder="City">
                </div>
                <div class="form-group">
                    <label for="state">State <span class="required">*</span></label>
                    <input type="text" id="state" name="state" required placeholder="State">
                </div>
            </div>

            <div class="form-group">
                <label for="pincode">Pincode</label>
                <input type="text" id="pincode" name="pincode" placeholder="Pincode">
            </div>

            <!-- Contact Information -->
            <div class="form-row">
                <div class="form-group">
                    <label for="contact-phone">Contact Phone</label>
                    <input type="tel" id="contact-phone" name="contact_phone" placeholder="+91 XXXXX XXXXX">
                </div>
                <div class="form-group">
                    <label for="contact-email">Contact Email</label>
                    <input type="email" id="contact-email" name="contact_email" placeholder="your@email.com">
                </div>
            </div>

            <!-- Image Upload -->
            <div class="form-group">
                <label for="primary-image">Pet Photo</label>
                <input type="file" id="primary-image" name="primary_image" accept="image/*">
            </div>

            <button type="submit" class="submit-btn">
                <i class="fas fa-paper-plane"></i> Submit Rehoming Request
            </button>
        </form>
    </div>

    <!-- Scripts -->
    <script>
        $(document).ready(function () {
            // DOM Elements
            const $petTypeSelect = $('#pet-type-select');
            const $breedSelect = $('#breed-select');
            const $breedLoading = $('#breed-loading');
            const $form = $('#rehoming-form');

            // 1. Load Pet Types on Page Load
            loadPetTypes();

            // 2. Handle Pet Type Change
            $petTypeSelect.on('change', function () {
                const petTypeId = $(this).val();

                // Reset Breed Dropdown
                $breedSelect.html('<option value="">-- Select Pet Type First --</option>');
                $breedSelect.prop('disabled', true);

                if (petTypeId) {
                    fetchBreeds(petTypeId);
                }
            });

            // 3. Handle Form Submission
            $form.on('submit', function (e) {
                e.preventDefault();
                submitForm(this);
            });

            // --- Functions ---

            function loadPetTypes() {
                $.ajax({
                    url: 'api/get_pet_types.php',
                    method: 'GET',
                    dataType: 'json',
                    success: function (response) {
                        if (response.success && response.data) {
                            $petTypeSelect.empty().append('<option value="">-- Select Pet Type --</option>');

                            $.each(response.data, function (index, type) {
                                // Store icon if needed using data attribute
                                $petTypeSelect.append(
                                    $('<option></option>')
                                        .val(type.id)
                                        .text(type.name)
                                        .data('icon', type.icon)
                                );
                            });
                        } else {
                            showAlert('Failed to load pet types', 'error');
                        }
                    },
                    error: function (xhr, status, error) {
                        console.error('AJAX Error:', error);
                        showAlert('Error loading pet types. Please refresh.', 'error');
                    }
                });
            }

            function fetchBreeds(petTypeId) {
                // UI loading state
                $breedSelect.html('<option value="">Loading...</option>');
                $breedLoading.show();

                $.ajax({
                    url: 'api/get_breeds.php',
                    method: 'GET',
                    data: { pet_type_id: petTypeId },
                    dataType: 'json',
                    success: function (response) {
                        $breedLoading.hide();

                        if (response.success && response.data && response.data.length > 0) {
                            $breedSelect.empty().append('<option value="">-- Select Breed (Optional) --</option>');

                            // Loop through Breed Groups
                            $.each(response.data, function (i, group) {
                                const $optgroup = $('<optgroup>').attr('label', group.group_name);

                                // Loop through Breeds in Group
                                $.each(group.breeds, function (j, breed) {
                                    $optgroup.append(
                                        $('<option></option>').val(breed.id).text(breed.name)
                                    );
                                });

                                $breedSelect.append($optgroup);
                            });

                            // Add Unknown Option
                            $breedSelect.append('<option value="">Unknown / Mixed Breed</option>');
                            $breedSelect.prop('disabled', false);

                        } else {
                            $breedSelect.html('<option value="">No breeds found</option>');
                            $breedSelect.prop('disabled', false); // Allow selecting "empty" (implies unknown)
                        }
                    },
                    error: function (xhr, status, error) {
                        $breedLoading.hide();
                        console.error('AJAX Error:', error);
                        $breedSelect.html('<option value="">Error loading breeds</option>');
                    }
                });
            }

            function submitForm(formElement) {
                const formData = new FormData(formElement);
                const $btn = $(formElement).find('.submit-btn');
                const originalText = $btn.html();

                $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Submitting...');

                $.ajax({
                    url: 'api/submit_rehoming.php',
                    method: 'POST',
                    data: formData,
                    contentType: false,
                    processData: false,
                    success: function (response) {
                        if (response.success) {
                            showAlert('Success! Your listing is pending approval.', 'success');
                            formElement.reset();
                            $breedSelect.html('<option value="">-- Select Pet Type First --</option>').prop('disabled', true);
                            window.scrollTo({ top: 0, behavior: 'smooth' });
                        } else {
                            showAlert(response.error || 'Submission failed', 'error');
                        }
                    },
                    error: function (xhr, status, error) {
                        console.error('Submission Error:', error);
                        showAlert('Server error occurred. Please try again.', 'error');
                    },
                    complete: function () {
                        $btn.prop('disabled', false).html(originalText);
                    }
                });
            }

            function showAlert(message, type) {
                const $container = $('#alert-container');
                $container.text(message)
                    .removeClass('success error')
                    .addClass('alert ' + type)
                    .fadeIn();

                setTimeout(function () {
                    $container.fadeOut();
                }, 5000);
            }
        });
    </script>
</body>

</html>