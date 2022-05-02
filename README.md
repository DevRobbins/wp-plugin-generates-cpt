# wp-plugin-generates-cpt
Please create a simple WordPress plugin that adds a new custom post type for ‘Tours’. This CPT
should include the capability to save revision history, and have the standard post tag taxonomy. In addition,
create a custom taxonomy and attach it to the CPT . The new taxonomy should be called ‘Types’ and will be
used to track the type of a tour (Cruise, Adventure, Relaxed, etc)
Finally, add two custom fields (post meta) to this CPT. The first should called ‘Tour Code’ that allows
users to enter and save only uppercase-alphanumeric strings (such as THXA, POR22, or CRK). Secondly add
a field called ‘Departure Date’, that will accept a date that the tour leaves. (Accomplish these without using
another plugin like ACF, but with custom PHP code).
