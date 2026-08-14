<section class="contact-section py-5">
    <div class="container">
        <div class="row g-5">
            <div class="col-lg-5">
                <span class="section-tag">CONTACT DETAILS</span>
                <h2 class="section-title mt-3">We're Here to Help</h2>
                <p class="mt-4">Whether you have a question about our services or want to book a visit, feel free to contact us.</p>

                <div class="contact-info mt-5">
                    <div class="contact-item">
                        <i class="bi bi-geo-alt"></i>
                        <div><h6>Address</h6><p>Pikkukouluntie 4,<br>02770 Espoo</p></div>
                    </div>
                    <div class="contact-item">
                        <i class="bi bi-telephone"></i>
                        <div><h6>Phone</h6><p><a href="tel:+358451566199">+358 45 156 6199</a></p></div>
                    </div>
                    <div class="contact-item">
                        <i class="bi bi-envelope"></i>
                        <div><h6>Email</h6><p><a href="mailto:info@yetiautohuolto.fi">info@yetiautohuolto.fi</a></p></div>
                    </div>
                    <div class="contact-item">
                        <i class="bi bi-clock"></i>
                        <div><h6>Opening Hours</h6><p>Mon – Fri : 08:00 – 18:00<br>Saturday : 09:00 – 15:00<br>Sunday : Closed</p></div>
                    </div>
                </div>
            </div>

            <div class="col-lg-7">
                <div class="contact-form-wrapper">
                    <h4 class="mb-4">Send Us a Message</h4>
                    <form method="POST" action="<?= e(url('/contact/')) ?>" novalidate>
                        <?= csrf_field() ?>

                        <div class="visually-hidden" aria-hidden="true">
                            <label for="contact-website">Website</label>
                            <input id="contact-website" type="text" name="website" tabindex="-1" autocomplete="off">
                        </div>

                        <?php if ($errors !== []): ?>
                            <div class="alert alert-danger" role="alert">Please correct the highlighted fields below.</div>
                        <?php endif; ?>

                        <div class="mb-3">
                            <label class="form-label" for="contact_full_name">Full Name *</label>
                            <input id="contact_full_name" type="text" name="full_name" class="form-control<?= field_error($errors, 'full_name') ? ' is-invalid' : '' ?>" placeholder="Your full name" maxlength="100" value="<?= e(form_value($values, 'full_name')) ?>" required autocomplete="name">
                            <?php if (field_error($errors, 'full_name')): ?><div class="invalid-feedback"><?= e(field_error($errors, 'full_name')) ?></div><?php endif; ?>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label" for="contact_phone">Phone Number</label>
                                <input id="contact_phone" type="tel" name="phone_number" class="form-control<?= field_error($errors, 'phone_number') ? ' is-invalid' : '' ?>" placeholder="+358..." maxlength="25" value="<?= e(form_value($values, 'phone_number')) ?>" autocomplete="tel">
                                <?php if (field_error($errors, 'phone_number')): ?><div class="invalid-feedback"><?= e(field_error($errors, 'phone_number')) ?></div><?php endif; ?>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label" for="contact_email">Email *</label>
                                <input id="contact_email" type="email" name="email" class="form-control<?= field_error($errors, 'email') ? ' is-invalid' : '' ?>" placeholder="name@email.com" maxlength="254" value="<?= e(form_value($values, 'email')) ?>" required autocomplete="email">
                                <?php if (field_error($errors, 'email')): ?><div class="invalid-feedback"><?= e(field_error($errors, 'email')) ?></div><?php endif; ?>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label" for="contact_subject">Subject</label>
                            <input id="contact_subject" type="text" name="subject" class="form-control<?= field_error($errors, 'subject') ? ' is-invalid' : '' ?>" placeholder="How can we help?" maxlength="150" value="<?= e(form_value($values, 'subject')) ?>">
                            <?php if (field_error($errors, 'subject')): ?><div class="invalid-feedback"><?= e(field_error($errors, 'subject')) ?></div><?php endif; ?>
                        </div>

                        <div class="mb-4">
                            <label class="form-label" for="contact_message">Message *</label>
                            <textarea id="contact_message" name="message" rows="6" class="form-control<?= field_error($errors, 'message') ? ' is-invalid' : '' ?>" maxlength="3000" placeholder="Write your message here..." required><?= e(form_value($values, 'message')) ?></textarea>
                            <?php if (field_error($errors, 'message')): ?><div class="invalid-feedback"><?= e(field_error($errors, 'message')) ?></div><?php endif; ?>
                        </div>

                        <button type="submit" class="btn btn-warning px-5 py-3">Send Message</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>
