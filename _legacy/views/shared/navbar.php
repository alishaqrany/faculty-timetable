<?php
if (!isset($navPrefix)) {
    $navPrefix = '';
}
?>
<div class="navbar">
    <a class="logo" href="<?php echo $navPrefix; ?>index.php">الرئيسية</a>
    <div class="menu-toggle">
        <span></span>
        <span></span>
        <span></span>
    </div>
    <div class="nav-links">
        <a href="<?php echo $navPrefix; ?>departments/departments.php">الاقسام</a>
        <a href="<?php echo $navPrefix; ?>sections/sections.php">الشعب</a>
        <a href="<?php echo $navPrefix; ?>levels/levels.php">الفرق</a>
        <a href="<?php echo $navPrefix; ?>members/faculty_members.php">اعضاء هيئة التدريس</a>
        <a href="<?php echo $navPrefix; ?>sessions/sessions.php">الفترات</a>
        <a href="<?php echo $navPrefix; ?>classrooms/classrooms.php">القاعات</a>
        <a href="<?php echo $navPrefix; ?>sections/sections.php">السكاشن</a>
        <a href="<?php echo $navPrefix; ?>subjects/subjects.php">المواد</a>
        <a href="<?php echo $navPrefix; ?>membercourses/membercourses.php">توزيع المواد</a>
        <a href="<?php echo $navPrefix; ?>scheduling.php">تسكين المواد</a>
        <a href="<?php echo $navPrefix; ?>table_query.php">عرض الجدول</a>
    </div>
</div>
<script>
    const menuToggle = document.querySelector('.menu-toggle');
    const navLinks = document.querySelector('.nav-links');

    if (menuToggle && navLinks) {
        menuToggle.addEventListener('click', () => {
            menuToggle.classList.toggle('active');
            navLinks.classList.toggle('active');
        });
    }
</script>
