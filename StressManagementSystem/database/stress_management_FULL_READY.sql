-- ============================================================
-- STRESSCARE / STRESS MANAGEMENT SYSTEM
-- COMPLETE DATABASE - READY FOR XAMPP phpMyAdmin IMPORT
-- ============================================================

CREATE DATABASE IF NOT EXISTS stress_management
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;

USE stress_management;

SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS recommendation_rules;
DROP TABLE IF EXISTS recommendations;
DROP TABLE IF EXISTS stress_assessments;
DROP TABLE IF EXISTS admins;
DROP TABLE IF EXISTS users;

SET FOREIGN_KEY_CHECKS = 1;

-- ============================================================
-- USERS
-- ============================================================

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- ADMINS
-- Username: admin
-- Password: admin123
-- ============================================================

CREATE TABLE admins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO admins (username, password)
VALUES ('admin', '$2y$12$MRcKai1x9aOO9UeIjUr54.IZwjzTCXlp89QEP7Q58E7IDljdka7.O');

-- ============================================================
-- STRESS ASSESSMENTS
-- ============================================================

CREATE TABLE stress_assessments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    stress_level INT NOT NULL,
    mood VARCHAR(100) NOT NULL,
    emotion VARCHAR(100) DEFAULT NULL,
    situation VARCHAR(150) DEFAULT NULL,
    activities TEXT,
    sleep_hours DECIMAL(4,1) DEFAULT 0,
    notes TEXT,
    analysis VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_assessment_user
        FOREIGN KEY (user_id)
        REFERENCES users(id)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- GENERAL RECOMMENDATIONS TABLE
-- ============================================================

CREATE TABLE recommendations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(150) NOT NULL,
    description TEXT NOT NULL,
    category VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- PERSONALIZED RECOMMENDATION RULES
-- ============================================================

CREATE TABLE recommendation_rules (
    id INT AUTO_INCREMENT PRIMARY KEY,
    emotion VARCHAR(100) NOT NULL,
    situation VARCHAR(150) NOT NULL,
    min_stress INT NOT NULL DEFAULT 1,
    max_stress INT NOT NULL DEFAULT 10,
    title VARCHAR(200) NOT NULL,
    description TEXT NOT NULL,
    activity VARCHAR(255),
    category VARCHAR(100),
    priority INT NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- PERSONALIZED RECOMMENDATIONS
-- ============================================================

INSERT INTO recommendation_rules
(emotion,situation,min_stress,max_stress,title,description,activity,category,priority)
VALUES

('Sad','Friends / Friendship',1,10,'Give Yourself Space',
'Friendship problems can feel heavy. Give yourself time to process your feelings instead of blaming yourself.',
'Write down what happened and one thing you would like to communicate calmly.','Friends',3),

('Angry','Friends / Friendship',1,10,'Pause Before Responding',
'When emotions are strong, a short pause can help prevent an argument from becoming worse.',
'Take a few slow breaths before replying to a message or continuing a difficult conversation.','Friends',3),

('Lonely','Friends / Friendship',1,10,'Reconnect With Someone',
'Feeling disconnected does not mean you have to stay alone. One small connection can make a difference.',
'Message or call one trusted friend today.','Support',3),

('Sad','Relationship / Love',1,10,'Be Gentle With Yourself',
'Relationship difficulties can affect your mood deeply. Your feelings are valid and you can take things one day at a time.',
'Spend some time doing an activity that normally helps you feel calm.','Relationship',3),

('Anxious','Relationship / Love',1,10,'Focus on What You Can Control',
'Uncertainty in relationships can create many what-if thoughts. Focus on actions that are actually within your control.',
'Write down what you can control and what you need to let go of for now.','Relationship',3),

('Hurt','Relationship / Love',1,10,'Give Yourself Recovery Time',
'It is okay to need time after being hurt. Recovery does not have to happen immediately.',
'Take a break from repeated checking or rereading difficult conversations.','Self Care',3),

('Angry','Family Problems',1,10,'Create a Calm Pause',
'Family disagreements can become more difficult when everyone is upset. A pause can create space for a calmer conversation.',
'Step away for a few minutes and return when you feel calmer.','Family',3),

('Anxious','Family Problems',1,10,'Separate Expectations From Your Worth',
'Family expectations can create pressure, but expectations do not define your value.',
'Write down one expectation that is worrying you and one realistic step you can take.','Mindset',3),

('Anxious','College / Studies',1,10,'Break Your Work Into Small Tasks',
'Academic pressure often feels bigger when everything is treated as one huge task.',
'Choose your most important task and work on it for 25 minutes.','Studies',4),

('Overwhelmed','College / Studies',1,10,'Use a Simple Study Plan',
'A short plan can reduce the feeling of having too much to do.',
'Write your top three study tasks for today and complete them one at a time.','Time Management',4),

('Fearful','College / Studies',1,10,'Focus on Preparation, Not Perfection',
'Fear about exams or grades can become smaller when you focus on the preparation you can control.',
'Choose one topic and complete one focused study session.','Studies',3),

('Anxious','Work / Career',1,10,'Focus on Your Next Career Step',
'Career uncertainty can feel overwhelming when you try to solve your entire future at once.',
'Choose one practical action such as improving your resume or learning one skill.','Career',3),

('Fearful','Work / Career',1,10,'Turn Uncertainty Into a Plan',
'You may not control every career outcome, but you can control your preparation.',
'Write one short-term and one long-term career goal.','Career',3),

('Anxious','Financial Problems',1,10,'Break Financial Worries Into Steps',
'Money concerns can feel overwhelming when they stay as one large problem.',
'Write your essential expenses and identify one expense you can review today.','Finance',4),

('Overwhelmed','Financial Problems',1,10,'Focus On What You Can Manage',
'You do not need to solve your entire financial situation in one day.',
'Choose one practical financial task and complete only that task today.','Finance',4),

('Anxious','Social Pressure',1,10,'You Do Not Have To Please Everyone',
'Trying to satisfy every expectation can be exhausting. Your wellbeing matters too.',
'Identify one expectation that is not necessary for you to carry today.','Mindset',3),

('Fearful','Social Pressure',1,10,'Reduce Comparison',
'Other peoples progress does not determine your value or your timeline.',
'Take a short break from comparison-heavy social media content.','Self Care',3),

('Sad','Breakup / Loss',1,10,'Allow Yourself Time',
'Healing after a loss is not a race. It is normal to need time.',
'Do one gentle activity today that gives you a sense of comfort.','Self Care',4),

('Lonely','Breakup / Loss',1,10,'Stay Connected',
'Loss can make you withdraw from people, but supportive connection can make recovery easier.',
'Spend some time with someone you trust rather than isolating yourself.','Support',4),

('Lonely','Loneliness',1,10,'Start With One Connection',
'You do not need a large social circle to begin feeling more connected.',
'Send one simple message to someone you feel comfortable with.','Support',4),

('Sad','Loneliness',1,10,'Create Small Moments of Connection',
'Small interactions can gradually reduce feelings of isolation.',
'Spend time in a shared space, class, activity, club, or community you enjoy.','Support',3),

('Angry','Conflict / Anger',1,10,'Pause Before Reacting',
'A short pause can help you respond to conflict rather than reacting immediately.',
'Take five slow breaths before speaking or replying.','Relaxation',4),

('Frustrated','Conflict / Anger',1,10,'Identify The Real Problem',
'Sometimes frustration becomes larger when the underlying issue is unclear.',
'Write down what happened, how you felt, and what outcome you actually want.','Mindset',3),

('Disappointed','Self Confidence',1,10,'Remember Your Progress',
'One disappointing result does not erase the progress you have already made.',
'Write down three things you have improved at over time.','Self Care',4),

('Fearful','Self Confidence',1,10,'Take One Small Challenge',
'Confidence grows through action, not by waiting until you feel completely confident.',
'Choose one small task you have been avoiding and try it today.','Self Care',4),

('Overwhelmed','Time Management',1,10,'Choose Three Priorities',
'Trying to do everything at once can make your workload feel impossible.',
'Write your top three tasks and ignore non-essential tasks until those are done.','Time Management',4),

('Anxious','Time Management',1,10,'Use Short Focus Sessions',
'Short focused sessions can make large workloads feel more manageable.',
'Try a 25-minute focused work session followed by a short break.','Time Management',3),

('Tired','Sleep Problems',1,10,'Protect Your Sleep Routine',
'Consistent sleep habits can help your body and mind recover.',
'Keep a regular bedtime and reduce stimulating screen use before sleep.','Sleep',4),

('Anxious','Sleep Problems',1,10,'Create A Calmer Night',
'Worry can make it harder to settle down. A predictable wind-down routine can help.',
'Spend 20 to 30 minutes doing a quiet activity before bed.','Sleep',4),

('Sad','Social Media',1,10,'Take A Comparison Break',
'Social media often shows selected highlights rather than the full reality of peoples lives.',
'Take a short break from comparison-heavy content.','Social Media',3),

('Anxious','Social Media',1,10,'Set A Small Screen Boundary',
'Reducing repeated checking can give your mind more space to relax.',
'Choose one period of the day when you will keep your phone away.','Social Media',3),

('Stressed','General Stress',1,10,'Take One Step At A Time',
'You do not need to solve everything today. Small consistent actions can make difficult periods easier.',
'Choose one important task and complete only that task first.','Mindset',5),

('Overwhelmed','General Stress',1,10,'Give Your Mind A Reset',
'When everything feels urgent, a short reset can help you decide what really needs attention.',
'Take five slow breaths and write down your top three priorities.','Relaxation',5),

('Calm','General Stress',1,10,'Protect Your Calm',
'You are already experiencing some calm. Keep building habits that support that balance.',
'Continue one activity that helps you feel relaxed.','Wellness',3),

('Happy','General Stress',1,10,'Keep Your Positive Habits',
'Positive routines can help maintain emotional balance even during stressful periods.',
'Notice one thing that went well today and continue a healthy activity.','Wellness',3),

('Neutral','General Stress',1,10,'Build A Healthy Routine',
'Even when you feel neutral, small healthy habits can help protect your emotional wellbeing.',
'Choose one healthy activity such as walking, stretching, reading, or talking with someone you trust.','Wellness',3),

('Tired','General Stress',1,10,'Give Yourself Time To Recharge',
'Feeling tired is a sign that rest and recovery deserve some attention.',
'Take a short break and protect enough time for sleep tonight.','Self Care',4),

('Frustrated','General Stress',1,10,'Step Away For A Moment',
'Frustration can make problems feel larger. A short reset can help you approach them more clearly.',
'Take a short walk or five slow breaths before returning to the task.','Relaxation',4),

('Anxious','General Stress',1,10,'Focus On What You Can Control',
'Anxiety often grows when your mind focuses on uncertain future outcomes.',
'Write down what you can control today and choose one small action.','Mindset',4);

-- ============================================================
-- DEMO USER
-- Email: demo@stresscare.local
-- Password: demo123
-- ============================================================

INSERT INTO users (name, email, password)
VALUES (
    'Demo User',
    'demo@stresscare.local',
    '$2y$12$GU7ySNhP68ffBQyRV3.T5OGFIQSfi365xfbNVnAebiweuXoFNPT4m'
);

-- ============================================================
-- END
-- ============================================================
