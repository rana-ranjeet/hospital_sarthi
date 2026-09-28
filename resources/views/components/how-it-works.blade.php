<section class="how-it-works-section" id="how-it-works">

    <div class="how-it-works-container">

        {{-- Section Heading --}}
        <div class="how-it-works-heading">

            <p class="how-it-works-eyebrow">
                SIMPLE &amp; EASY
            </p>

            <h2>
    How It Works
</h2>

            <p>
                Choose a service, book your companion, and make your hospital visit easier.
            </p>

        </div>


        {{-- Steps --}}
        <div class="how-it-works-grid">

            {{-- Step 01 --}}
            <div class="how-it-works-card">

                <span class="how-it-works-number">
                    01
                </span>

                <div class="how-it-works-icon">
                    <i class="bi bi-list-check"></i>
                </div>

                <h3>
                    Choose Your Assistance
                </h3>

                <p>
                    Select the assistance plan that matches your hospital visit —
                    OPD, Doctor, Lab, Complete Hospital Assistance, Hourly or Full-Day.
                </p>

            </div>


            {{-- Step 02 --}}
            <div class="how-it-works-card">

                <span class="how-it-works-number">
                    02
                </span>

                <div class="how-it-works-icon">
                    <i class="bi bi-whatsapp"></i>
                </div>

                <h3>
                    Book Your Companion
                </h3>

                <p>
                    Click on Book Now and share your hospital visit details
                    with our team through WhatsApp.
                </p>

            </div>


            {{-- Step 03 --}}
            <div class="how-it-works-card">

                <span class="how-it-works-number">
                    03
                </span>

                <div class="how-it-works-icon">
                    <i class="bi bi-calendar-check"></i>
                </div>

                <h3>
                    We Confirm Your Booking
                </h3>

                <p>
                    Our team will contact you and confirm your hospital,
                    date, time and assistance requirements.
                </p>

            </div>


            {{-- Step 04 --}}
            <div class="how-it-works-card">

                <span class="how-it-works-number">
                    04
                </span>

                <div class="how-it-works-icon">
                    <i class="bi bi-person-check"></i>
                </div>

                <h3>
                    Meet Your Companion
                </h3>

                <p>
                    Your companion will meet you at the hospital and help
                    you with the practical steps of your visit.
                </p>

            </div>


            {{-- Step 05 --}}
            <div class="how-it-works-card">

                <span class="how-it-works-number">
                    05
                </span>

                <div class="how-it-works-icon">
                    <i class="bi bi-heart-pulse"></i>
                </div>

                <h3>
                    Complete Your Visit
                </h3>

                <p>
                    Get assistance with registration, navigation, doctor visits,
                    tests, billing and reports — with someone by your side.
                </p>

            </div>

        </div>


        {{-- Bottom CTA --}}
        <div class="how-it-works-bottom">

            <div class="how-it-works-bottom-content">

                <div class="how-it-works-bottom-text">

                    <h3>
                        Need help choosing?
                    </h3>

                    <p>
                        Tell us what you need and we'll help you choose the right service.
                    </p>

                </div>


                <a
                    href="https://wa.me/919522104158?text={{ urlencode('Hello Hospital Sarthi, I need help choosing the right assistance plan.') }}"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="how-it-works-button"
                >
                    <i class="bi bi-whatsapp"></i>
                    Talk to Us
                </a>

            </div>

        </div>

    </div>

    </section>


<style>

/* =========================================
   HOW IT WORKS
========================================= */

.how-it-works-section {
    /* padding: 85px 20px; */
    background: #f7faf8;
}

.how-it-works-container {
    width: 100%;
    max-width: 1200px;
    margin: 0 auto;
}


/* =========================================
   HEADING
========================================= */

.how-it-works-heading {
    max-width: 760px;
    margin: 0 0 50px;
    text-align: left;
}

.how-it-works-eyebrow {
    margin: 0 0 10px;
    color: #198754;
    font-size: 13px;
    font-weight: 800;
    letter-spacing: 2px;
}

.how-it-works-heading h2 {
    margin: 0 0 14px;
    color: #183b2b;
    font-size: clamp(32px, 5vw, 48px);
    font-weight: 700;
    line-height: 1.15;
}

.how-it-works-heading > p:last-child {
    max-width: 700px;
    margin: 0;
    color: #66736d;
    font-size: 16px;
    line-height: 1.7;
}


/* =========================================
   STEPS GRID
========================================= */

.how-it-works-grid {
    display: grid;
    grid-template-columns: repeat(5, 1fr);
    gap: 18px;
}


/* =========================================
   STEP CARD
========================================= */

.how-it-works-card {
    position: relative;
    padding: 30px 22px;
    background: #ffffff;
    border: 1px solid #e4ebe7;
    border-radius: 18px;
    text-align: center;
    transition:
        transform 0.25s ease,
        box-shadow 0.25s ease,
        border-color 0.25s ease;
}

.how-it-works-card:hover {
    transform: translateY(-5px);
    border-color: #b9d8c7;
    box-shadow: 0 15px 35px rgba(24, 59, 43, 0.08);
}


/* =========================================
   NUMBER
========================================= */

.how-it-works-number {
    position: absolute;
    top: 14px;
    right: 17px;
    color: #dcebe3;
    font-size: 27px;
    font-weight: 800;
    line-height: 1;
}


/* =========================================
   ICON
========================================= */

.how-it-works-icon {
    width: 58px;
    height: 58px;
    margin: 5px auto 22px;

    display: flex;
    align-items: center;
    justify-content: center;

    border-radius: 50%;
    background: #eaf6ef;
    color: #198754;

    font-size: 25px;
}


/* =========================================
   CARD TITLE
========================================= */

.how-it-works-card h3 {
    margin: 0 0 12px;
    color: #183b2b;
    font-size: 18px;
    font-weight: 700;
    line-height: 1.35;
}


/* =========================================
   CARD TEXT
========================================= */

.how-it-works-card p {
    margin: 0;
    color: #68766f;
    font-size: 14px;
    line-height: 1.65;
}


/* =========================================
   BOTTOM CTA
========================================= */

.how-it-works-bottom {
    max-width: 900px;
    margin: 35px 0 0;
    padding: 20px 25px;

    background: #183b2b;
    border-radius: 14px;
}


/* CTA CONTENT */

.how-it-works-bottom-content {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 25px;
}


/* CTA TEXT */

.how-it-works-bottom-text {
    min-width: 0;
}

.how-it-works-bottom h3 {
    margin: 0 0 5px;
    color: #ffffff;
    font-size: 20px;
    font-weight: 700;
    line-height: 1.3;
}

.how-it-works-bottom p {
    margin: 0;
    color: #d8e7df;
    font-size: 14px;
    line-height: 1.5;
}


/* =========================================
   WHATSAPP BUTTON
========================================= */

.how-it-works-button {
    flex-shrink: 0;

    display: inline-flex;
    align-items: center;
    justify-content: center;

    gap: 7px;

    padding: 10px 18px;

    border-radius: 8px;

    background: #25d366;
    color: #ffffff !important;

    text-decoration: none !important;

    font-size: 13px;
    font-weight: 700;

    white-space: nowrap;

    transition:
        background 0.2s ease,
        transform 0.2s ease;
}

.how-it-works-button:hover {
    background: #20bd5a;
    color: #ffffff !important;
    transform: translateY(-2px);
}


/* =========================================
   TABLET
========================================= */

@media (max-width: 1100px) {

    .how-it-works-grid {
        grid-template-columns: repeat(3, 1fr);
    }

}


/* =========================================
   MOBILE
========================================= */

@media (max-width: 700px) {

    .how-it-works-section {
        padding: 38px 16px;
    }

    .how-it-works-heading {
        margin-bottom: 22px;
    }

    .how-it-works-heading h2 {
        font-size: 34px;
    }

    .how-it-works-grid {
        grid-template-columns: 1fr;
        gap: 10px;
    }

    .how-it-works-card {
        padding: 18px 16px;
    }

    .how-it-works-bottom {
        margin-top: 16px;
        padding: 16px;
    }

    .how-it-works-bottom-content {
        flex-direction: column;
        align-items: center;
        gap: 10px;
    }

    .how-it-works-button {
        width: 100%;
    }

}
</style>
