# خطة تطوير تطبيق الهاتف (Flutter) - Roadmap

## الهدف

تطوير التطبيق من النسخة الحالية إلى نسخة إنتاجية متكاملة للطلاب والإدارة مع جودة عالية وقابلية توسع.

## المرحلة 0 - الوضع الحالي (Completed ✅)

- إنشاء تطبيق Flutter أساسي.
- تنفيذ تسجيل الدخول + إدارة الجلسة عبر Provider.
- تصميم واجهة Material 3 مع دعم عربي (RTL).
- تنفيذ شاشات الطالب:
  - جدول الفرقة (YearTimetablePage).
  - جدول السكشن (SectionTimetablePage).
  - محاضرات اليوم (TodayLecturesPage).
- تنفيذ API v1:
  - Auth (login, logout, me)
  - Student (timetable, section-timetable, today-lectures)
  - Lookups (departments, levels, sections, subjects, sessions, classrooms)
  - Admin phase 1 (CRUD for departments, subjects, sections, sessions)
- إضافة migration للفهارس (035).
- إضافة Provider لإدارة الحالة (AuthProvider, TimetableProvider).
- إضافة CacheManager للتخزين المؤقت المحلي.
- إنشاء عناصر واجهة مشتركة (EmptyState, ErrorState, LoadingOverlay, LectureCard).
- تطبيق AppTheme متقدم مع خطوط عربية.
- نجاح `flutter analyze` و `flutter test`.

## المرحلة 1 - تثبيت جودة الواجهة (1-2 أسبوع)

المخرجات:
- تحسين UX/UI للشاشات الحالية (حالات التحميل، الحالات الفارغة، حالات الخطأ) — ✅ منفذ جزئياً.
- إضافة فلاتر أكثر وضوحاً (اليوم، القسم، الفرقة، السكشن).
- دعم pagination فعلي في شاشات القوائم الكبيرة.
- تحسين الرسائل العربية وتوحيد النصوص.

المعايير:
- زمن استجابة مقبول للشاشات الأساسية.
- لا يوجد crash في تدفق الطالب الأساسي.

## المرحلة 2 - إكمال الإدارة على الموبايل (2-4 أسابيع)

المخرجات:
- شاشات إدارة (CRUD) للكيانات:
  - Departments
  - Subjects
  - Sections
  - Sessions
- إضافة صلاحيات واجهة حسب الدور.
- validation على مستوى الواجهة قبل الإرسال.

المعايير:
- جميع عمليات CRUD الإدارية تنجح من الموبايل.
- احترام الصلاحيات دون تجاوز.

## المرحلة 3 - الأمان والإنتاجية (1-2 أسبوع)

المخرجات:
- نقل التخزين المحلي للـ token إلى `flutter_secure_storage`.
- دعم refresh/expiry strategy للجلسة.
- إضافة audit traces لعمليات admin API المهمة.
- تحسين سياسات rate limit لمسارات حساسة.

المعايير:
- جلسات أكثر أماناً.
- مراقبة أفضل لسلوك API.

## المرحلة 4 - الأداء والاعتمادية (1-2 أسبوع)

المخرجات:
- تعزيز CacheManager بسياسات TTL متقدمة — ✅ منفذ جزئياً.
- retry strategy للشبكة.
- تقليل payloads وإضافة lightweight responses عند الحاجة.
- profiling لاستعلامات الجدول الثقيلة.

المعايير:
- زمن تحميل أقل.
- تجربة أفضل في الشبكات الضعيفة.

## المرحلة 5 - الاختبارات والإطلاق (1-2 أسبوع)

المخرجات:
- توسيع Flutter tests (Widget + integration).
- smoke test automation لـ API v1.
- checklist release:
  - Versioning
  - Changelog
  - Regression pass

المعايير:
- جودة مستقرة قبل أي نشر.
- وجود آلية rollback بسيطة.

## Backlog مقترح (بعد MVP)

- إشعارات push لمحاضرات اليوم والتغييرات.
- Offline mode كامل لآخر جداول تمت مزامنتها.
- تقويم أسبوعي تفاعلي.
- دعم تعدد اللغات (عربي/إنجليزي) الكامل.
- تحليلات استخدام داخل التطبيق.
- شاشات إدارة الأولوية من الموبايل.
- عرض التقارير والإحصائيات من الموبايل.

## إدارة المخاطر

- خطر: تضارب تغييرات API بين الويب والموبايل.
  - الإجراء: تثبيت contract v1 وعدم كسر endpoints.
- خطر: بطء الاستعلامات مع بيانات أكبر.
  - الإجراء: فهارس إضافية + pagination + profiling.
- خطر: أخطاء صلاحيات.
  - الإجراء: اختبارات صلاحيات لكل endpoint إداري.

## مؤشرات نجاح KPI

- معدل نجاح تسجيل الدخول.
- متوسط زمن تحميل شاشة محاضرات اليوم.
- نسبة الأخطاء 4xx/5xx في `api/v1`.
- معدل الأعطال crash-free sessions.
- نسبة إتمام تدفق الطالب (Login → Today Lectures).
- نسبة استخدام التخزين المؤقت (cache hit rate).
