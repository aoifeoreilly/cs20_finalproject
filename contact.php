<?php include 'header.php'; ?>

<section class="hero">
    <div class="hero-inner">
        <div class="hero-text">
            <p class="section-label">Tufts University - Contact</p>
            <h1>We'd love to hear from you.</h1>
            <p>
                Questions, feedback, or ideas for TuftsEats? Send us a message and
                we'll get back to you as soon as we can.
            </p>
        </div>

        <div class="builder-card">
            <h3>Send a Message</h3>
            <p class="subtitle">Reach out to the TuftsEats team</p>

            <form action="#" method="get">
                <div class="form-group">
                    <label for="fullname">Full Name</label>
                    <input type="text" id="fullname" name="fullname" placeholder="Your name">
                </div>

                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email" placeholder="you@tufts.edu">
                </div>

                <div class="form-group">
                    <label for="topic">Topic</label>
                    <select id="topic" name="topic">
                        <option>General Question</option>
                        <option>Feedback</option>
                        <option>Recipe Suggestion</option>
                        <option>Technical Issue</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="message">Message</label>
                    <textarea id="message" name="message" placeholder="Write your message here"></textarea>
                </div>

                <button type="submit" class="btn btn-primary find-btn">Send Message</button>
            </form>
        </div>
    </div>
</section>

<section class="section how-section">
    <div class="section-inner">
        <div class="text-center">
            <p class="section-label">Contact Details</p>
            <h2>Other ways to reach us</h2>
            <div class="divider" style="margin: 1rem auto 0;"></div>
        </div>

        <div class="steps-grid">
            <div class="step-card">
                <div class="step-icon">@</div>
                <h4>Email</h4>
                <p>tseats@tufts.edu</p>
            </div>

            <div class="step-card">
                <div class="step-icon">☎</div>
                <h4>Phone</h4>
                <p>(617) 555-2040</p>
            </div>

            <div class="step-card">
                <div class="step-icon">⌂</div>
                <h4>Campus</h4>
                <p>Tufts University, Medford, MA</p>
            </div>
        </div>
    </div>
</section>

<style>
.form-group {
    cmargin-bottom: 1.5rem; 
}
.form-group label { 
    display: block; 
    margin-bottom: 0.5rem; 
    font-weight: 600; 
    color: #374151; 
}
.form-group input, .form-group select, .form-group textarea {
    width: 100%; 
    padding: 0.75rem; 
    border: 1px solid #d1d5db; 
    border-radius: 0.5rem; 
    font-family: inherit;
}
.form-group textarea { 
    min-height: 100px; 
    resize: vertical; 
}
</style>

<?php include 'footer.php'; ?>