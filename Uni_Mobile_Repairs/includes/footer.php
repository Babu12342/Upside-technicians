<?php
// includes/footer.php
?>
<footer style="background: #0f172a; color: #94a3b8; padding: 50px 20px 20px; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; position: relative; z-index: 100;">
    <div style="max-width: 1200px; margin: 0 auto; display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 40px; margin-bottom: 40px;">
        
        <!-- Column 1: Brand & Socials -->
        <div>
            <h3 style="color: #fff; display: flex; align-items: center; gap: 10px; font-size: 1.1rem; margin-bottom: 15px;">
                <i class="fas fa-tools" style="color: #0284c7;"></i> UNI MOBILE
            </h3>
            <p style="font-size: 0.9rem; line-height: 1.5; color: #94a3b8; margin-bottom: 20px;">
                Your trusted campus hub for certified device repairs, authentic electronics, and premium tech accessories.
            </p>
            <div class="social-icons" style="display: flex; gap: 10px; position: relative; z-index: 102;">
                <!-- Facebook -->
                <a href="https://www.facebook.com/profile.php?id=61564467094403" target="_blank" onclick="window.open(this.href, '_blank'); return false;" title="Facebook" style="display: inline-flex; align-items: center; justify-content: center; width: 35px; height: 35px; background: #1e293b; color: #fff; border-radius: 50%; text-decoration: none; cursor: pointer; transition: 0.2s;">
                    <i class="fab fa-facebook-f" style="pointer-events: none;"></i>
                </a>
                
                <!-- WhatsApp -->
                <a href="https://wa.me/message/C2BYVXRRSCCDB1" target="_blank" onclick="window.open(this.href, '_blank'); return false;" title="WhatsApp" style="display: inline-flex; align-items: center; justify-content: center; width: 35px; height: 35px; background: #1e293b; color: #fff; border-radius: 50%; text-decoration: none; cursor: pointer; transition: 0.2s;">
                    <i class="fab fa-whatsapp" style="pointer-events: none;"></i>
                </a>
                
                <!-- Twitter / X -->
                <a href="#" target="_blank" onclick="return false;" title="Twitter" style="display: inline-flex; align-items: center; justify-content: center; width: 35px; height: 35px; background: #1e293b; color: #fff; border-radius: 50%; text-decoration: none; cursor: pointer; transition: 0.2s;">
                    <i class="fab fa-twitter" style="pointer-events: none;"></i>
                </a>
                
                <!-- Instagram -->
                <a href="https://www.instagram.com/upsidetechnicians?utm_source=qr" target="_blank" onclick="window.open(this.href, '_blank'); return false;" title="Instagram" style="display: inline-flex; align-items: center; justify-content: center; width: 35px; height: 35px; background: #1e293b; color: #fff; border-radius: 50%; text-decoration: none; cursor: pointer; transition: 0.2s;">
                    <i class="fab fa-instagram" style="pointer-events: none;"></i>
                </a>
                
                <!-- YouTube -->
                <a href="https://youtube.com/@gamingcraftandrepairs?si=GyiomJHoVu9xfImb" target="_blank" onclick="window.open(this.href, '_blank'); return false;" title="YouTube" style="display: inline-flex; align-items: center; justify-content: center; width: 35px; height: 35px; background: #1e293b; color: #fff; border-radius: 50%; text-decoration: none; cursor: pointer; transition: 0.2s;">
                    <i class="fab fa-youtube" style="pointer-events: none;"></i>
                </a>
            </div>
        </div>

        <!-- Column 2: Customer Services -->
        <div>
            <h4 style="color: #fff; font-size: 0.95rem; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 20px;">Customer Services</h4>
            <ul style="list-style: none; padding: 0; margin: 0; display: flex; flex-direction: column; gap: 10px; font-size: 0.9rem;">
                <li><a href="help.php" style="color: #94a3b8; text-decoration: none;">Help Center</a></li>
                <li><a href="order_status.php" style="color: #94a3b8; text-decoration: none;">Order Status</a></li>
                <li><a href="book_repair.php" style="color: #94a3b8; text-decoration: none;">Book a Repair</a></li>
                <li><a href="return_policy.php" style="color: #94a3b8; text-decoration: none;">Return Policy</a></li>
                <li><a href="repairs.php" style="color: #94a3b8; text-decoration: none;">Repairs</a></li>
            </ul>
        </div>

        <!-- Column 3: Information -->
        <div>
            <h4 style="color: #fff; font-size: 0.95rem; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 20px;">Information</h4>
            <ul style="list-style: none; padding: 0; margin: 0; display: flex; flex-direction: column; gap: 10px; font-size: 0.9rem;">
                <li><a href="about.php" style="color: #94a3b8; text-decoration: none;">About Us</a></li>
                <li><a href="privacy.php" style="color: #94a3b8; text-decoration: none;">Privacy Policy</a></li>
                <li><a href="terms.php" style="color: #94a3b8; text-decoration: none;">Terms & Conditions</a></li>
            </ul>
        </div>

        <!-- Column 4: Contact Us -->
        <div>
            <h4 style="color: #fff; font-size: 0.95rem; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 20px;">Contact Us</h4>
            <ul style="list-style: none; padding: 0; margin: 0; display: flex; flex-direction: column; gap: 12px; font-size: 0.9rem;">
                <li style="display: flex; align-items: flex-start; gap: 10px;"><i class="fas fa-map-marker-alt" style="margin-top: 3px; color: #0284c7;"></i> Eldoret, Kenya</li>
                <li style="display: flex; align-items: center; gap: 10px;"><i class="fas fa-phone-alt" style="color: #0284c7;"></i> +254 700 000 000</li>
                <li style="display: flex; align-items: center; gap: 10px;"><i class="fas fa-envelope" style="color: #0284c7;"></i> info@unimobile.com</li>
            </ul>
        </div>

    </div>
    
    <div style="border-top: 1px solid #1e293b; padding-top: 20px; text-align: center; font-size: 0.85rem; color: #64748b;">
        &copy; <?php echo date('Y'); ?> UNI MOBILE. All rights reserved.
    </div>
</footer>