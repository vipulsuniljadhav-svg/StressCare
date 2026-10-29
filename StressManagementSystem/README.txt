STRESSCARE - STRESS MANAGEMENT SYSTEM

INSTALL:
1. Extract StressManagementSystem_Full into C:\xampp\htdocs\
2. Start Apache and MySQL in XAMPP.
3. Open http://localhost/phpmyadmin
4. Import database/stress_management.sql
5. Open http://localhost/StressManagementSystem_Full/

IMPORTANT PORT:
config/database.php uses port 3307. If XAMPP MySQL is on 3306, change $port=3307 to $port=3306.

ADMIN:
URL: http://localhost/StressManagementSystem_Full/admin/login.php
Username: admin
Password: admin123

DEMO USER:
Email: demo@stresscare.local
Password: demo123

FEATURES:
- User registration/login
- 15 category-specific assessment types
- 6 questions per selected category
- Automatic 1-10 stress score
- Emotion and sleep tracking
- Personalized recommendation engine
- Positive fallback recommendations when no database match exists
- Assessment history/report
- Admin dashboard
- Admin assessment list
- User list
- Recommendation rule management

Academic note: the score is a project-level stress indicator, not a medical diagnosis.
