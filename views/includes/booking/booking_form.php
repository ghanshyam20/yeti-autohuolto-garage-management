<section class="booking-form-section">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-xl-10">
                <div class="booking-form-wrapper">
                    <form method="POST" action="<?= e(url('/booking/')) ?>">
                        <?= csrf_field() ?>

                        <div class="visually-hidden" aria-hidden="true">
                            <label for="booking-website">Website</label>
                            <input id="booking-website" type="text" name="website" tabindex="-1" autocomplete="off">
                        </div>

                        <?php if ($errors !== []): ?>
                            <div class="alert alert-danger mb-4" role="alert">
                                <strong>Please correct the following:</strong>
                                <ul class="mb-0 mt-2">
                                    <?php foreach ($errors as $error): ?>
                                        <li><?= e($error) ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php endif; ?>

                        <div class="row g-4">
                            <div class="col-lg-6">
                                <h5 class="form-section-title">Customer Information</h5>

                                <div class="mb-3">
                                    <label class="form-label" for="full_name">Full Name <span class="text-danger">*</span></label>
                                    <input id="full_name" type="text" name="full_name" class="form-control<?= field_error($errors, 'full_name') ? ' is-invalid' : '' ?>" placeholder="John Smith" maxlength="100" value="<?= e(form_value($values, 'full_name')) ?>" required autocomplete="name">
                                    <?php if (field_error($errors, 'full_name')): ?><div class="invalid-feedback"><?= e(field_error($errors, 'full_name')) ?></div><?php endif; ?>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label" for="phone_number">Phone Number <span class="text-danger">*</span></label>
                                    <input id="phone_number" type="tel" name="phone_number" class="form-control<?= field_error($errors, 'phone_number') ? ' is-invalid' : '' ?>" placeholder="+358..." maxlength="25" value="<?= e(form_value($values, 'phone_number')) ?>" required autocomplete="tel">
                                    <?php if (field_error($errors, 'phone_number')): ?><div class="invalid-feedback"><?= e(field_error($errors, 'phone_number')) ?></div><?php endif; ?>
                                </div>

                                <div>
                                    <label class="form-label" for="email">Email Address <span class="text-danger">*</span></label>
                                    <input id="email" type="email" name="email" class="form-control<?= field_error($errors, 'email') ? ' is-invalid' : '' ?>" placeholder="name@email.com" maxlength="254" value="<?= e(form_value($values, 'email')) ?>" required autocomplete="email">
                                    <?php if (field_error($errors, 'email')): ?><div class="invalid-feedback"><?= e(field_error($errors, 'email')) ?></div><?php endif; ?>
                                </div>
                            </div>

                            <div class="col-lg-6">
                                <h5 class="form-section-title">Vehicle Information</h5>

                                <div class="mb-3">
                                    <label class="form-label" for="vehicle_make">Vehicle Make <span class="text-danger">*</span></label>
                                    <input id="vehicle_make" type="text" name="vehicle_make" class="form-control<?= field_error($errors, 'vehicle_make') ? ' is-invalid' : '' ?>" placeholder="Toyota" maxlength="100" value="<?= e(form_value($values, 'vehicle_make')) ?>" required>
                                    <?php if (field_error($errors, 'vehicle_make')): ?><div class="invalid-feedback"><?= e(field_error($errors, 'vehicle_make')) ?></div><?php endif; ?>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label" for="vehicle_model">Vehicle Model <span class="text-danger">*</span></label>
                                    <input id="vehicle_model" type="text" name="vehicle_model" class="form-control<?= field_error($errors, 'vehicle_model') ? ' is-invalid' : '' ?>" placeholder="Corolla" maxlength="100" value="<?= e(form_value($values, 'vehicle_model')) ?>" required>
                                    <?php if (field_error($errors, 'vehicle_model')): ?><div class="invalid-feedback"><?= e(field_error($errors, 'vehicle_model')) ?></div><?php endif; ?>
                                </div>

                                <div>
                                    <label class="form-label" for="registration_number">Registration Number</label>
                                    <input id="registration_number" type="text" name="registration_number" class="form-control<?= field_error($errors, 'registration_number') ? ' is-invalid' : '' ?>" placeholder="ABC-123" maxlength="20" value="<?= e(form_value($values, 'registration_number')) ?>">
                                    <?php if (field_error($errors, 'registration_number')): ?><div class="invalid-feedback"><?= e(field_error($errors, 'registration_number')) ?></div><?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <hr class="my-5">
                        <h5 class="form-section-title">Service Request</h5>

                        <div class="mb-4">
                            <label class="form-label" for="service_required">Service Required <span class="text-danger">*</span></label>
                            <select id="service_required" name="service_required" class="form-select<?= field_error($errors, 'service_required') ? ' is-invalid' : '' ?>" required>
                                <option value="">Select a service</option>
                                <?php foreach (service_choices() as $value => $label): ?>
                                    <option value="<?= e($value) ?>" <?= form_value($values, 'service_required') === $value ? 'selected' : '' ?>><?= e($label) ?></option>
                                <?php endforeach; ?>
                            </select>
                            <?php if (field_error($errors, 'service_required')): ?><div class="invalid-feedback"><?= e(field_error($errors, 'service_required')) ?></div><?php endif; ?>
                        </div>

                        <div class="mb-4">
                            <label class="form-label" for="problem_description">Describe the Problem</label>
                            <textarea id="problem_description" name="problem_description" class="form-control<?= field_error($errors, 'problem_description') ? ' is-invalid' : '' ?>" rows="5" maxlength="5000" placeholder="Describe the issue or service you need..."><?= e(form_value($values, 'problem_description')) ?></textarea>
                            <?php if (field_error($errors, 'problem_description')): ?><div class="invalid-feedback"><?= e(field_error($errors, 'problem_description')) ?></div><?php endif; ?>
                        </div>

                        <div class="row g-4">
                            <div class="col-md-6">
                                <label class="form-label" for="preferred_date">Preferred Date <span class="text-danger">*</span></label>
                                <input id="preferred_date" type="date" name="preferred_date" class="form-control<?= field_error($errors, 'preferred_date') ? ' is-invalid' : '' ?>" min="<?= e(date('Y-m-d')) ?>" value="<?= e(form_value($values, 'preferred_date')) ?>" required>
                                <?php if (field_error($errors, 'preferred_date')): ?><div class="invalid-feedback"><?= e(field_error($errors, 'preferred_date')) ?></div><?php endif; ?>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label" for="preferred_time">Preferred Time</label>
                                <select id="preferred_time" name="preferred_time" class="form-select<?= field_error($errors, 'preferred_time') ? ' is-invalid' : '' ?>">
                                    <?php foreach (time_choices() as $value => $label): ?>
                                        <option value="<?= e($value) ?>" <?= form_value($values, 'preferred_time', 'no_preference') === $value ? 'selected' : '' ?>><?= e($label) ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <?php if (field_error($errors, 'preferred_time')): ?><div class="invalid-feedback"><?= e(field_error($errors, 'preferred_time')) ?></div><?php endif; ?>
                            </div>
                        </div>

                        <div class="booking-note mt-5">
                            <i class="bi bi-info-circle"></i>
                            <span>This is a booking request only. We will contact you to confirm the final appointment time.</span>
                        </div>

                        <div class="text-center mt-5">
                            <button type="submit" class="btn btn-primary-custom px-5 py-3">Send Booking Request</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>
