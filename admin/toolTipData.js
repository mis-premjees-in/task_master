var FiltersEnabled = 0; // if your not going to use transitions or filters in any of the tips set this to 0
var spacer="&nbsp; &nbsp; &nbsp; ";

// email notifications to admin
notifyAdminNewMembers0Tip=["", spacer+"No email notifications to admin."];
notifyAdminNewMembers1Tip=["", spacer+"Notify admin only when a new member is waiting for approval."];
notifyAdminNewMembers2Tip=["", spacer+"Notify admin for all new sign-ups."];

// visitorSignup
visitorSignup0Tip=["", spacer+"If this option is selected, visitors will not be able to join this group unless the admin manually moves them to this group from the admin area."];
visitorSignup1Tip=["", spacer+"If this option is selected, visitors can join this group but will not be able to sign in unless the admin approves them from the admin area."];
visitorSignup2Tip=["", spacer+"If this option is selected, visitors can join this group and will be able to sign in instantly with no need for admin approval."];

// madb table
madb_addTip=["",spacer+"This option allows all members of the group to add records to the 'Master Anatomy DB' table. A member who adds a record to the table becomes the 'owner' of that record."];

madb_view0Tip=["",spacer+"This option prohibits all members of the group from viewing any record in the 'Master Anatomy DB' table."];
madb_view1Tip=["",spacer+"This option allows each member of the group to view only his own records in the 'Master Anatomy DB' table."];
madb_view2Tip=["",spacer+"This option allows each member of the group to view any record owned by any member of the group in the 'Master Anatomy DB' table."];
madb_view3Tip=["",spacer+"This option allows each member of the group to view all records in the 'Master Anatomy DB' table."];

madb_edit0Tip=["",spacer+"This option prohibits all members of the group from modifying any record in the 'Master Anatomy DB' table."];
madb_edit1Tip=["",spacer+"This option allows each member of the group to edit only his own records in the 'Master Anatomy DB' table."];
madb_edit2Tip=["",spacer+"This option allows each member of the group to edit any record owned by any member of the group in the 'Master Anatomy DB' table."];
madb_edit3Tip=["",spacer+"This option allows each member of the group to edit any records in the 'Master Anatomy DB' table, regardless of their owner."];

madb_delete0Tip=["",spacer+"This option prohibits all members of the group from deleting any record in the 'Master Anatomy DB' table."];
madb_delete1Tip=["",spacer+"This option allows each member of the group to delete only his own records in the 'Master Anatomy DB' table."];
madb_delete2Tip=["",spacer+"This option allows each member of the group to delete any record owned by any member of the group in the 'Master Anatomy DB' table."];
madb_delete3Tip=["",spacer+"This option allows each member of the group to delete any records in the 'Master Anatomy DB' table."];

// whats table
whats_addTip=["",spacer+"This option allows all members of the group to add records to the 'Whats (Tasks)' table. A member who adds a record to the table becomes the 'owner' of that record."];

whats_view0Tip=["",spacer+"This option prohibits all members of the group from viewing any record in the 'Whats (Tasks)' table."];
whats_view1Tip=["",spacer+"This option allows each member of the group to view only his own records in the 'Whats (Tasks)' table."];
whats_view2Tip=["",spacer+"This option allows each member of the group to view any record owned by any member of the group in the 'Whats (Tasks)' table."];
whats_view3Tip=["",spacer+"This option allows each member of the group to view all records in the 'Whats (Tasks)' table."];

whats_edit0Tip=["",spacer+"This option prohibits all members of the group from modifying any record in the 'Whats (Tasks)' table."];
whats_edit1Tip=["",spacer+"This option allows each member of the group to edit only his own records in the 'Whats (Tasks)' table."];
whats_edit2Tip=["",spacer+"This option allows each member of the group to edit any record owned by any member of the group in the 'Whats (Tasks)' table."];
whats_edit3Tip=["",spacer+"This option allows each member of the group to edit any records in the 'Whats (Tasks)' table, regardless of their owner."];

whats_delete0Tip=["",spacer+"This option prohibits all members of the group from deleting any record in the 'Whats (Tasks)' table."];
whats_delete1Tip=["",spacer+"This option allows each member of the group to delete only his own records in the 'Whats (Tasks)' table."];
whats_delete2Tip=["",spacer+"This option allows each member of the group to delete any record owned by any member of the group in the 'Whats (Tasks)' table."];
whats_delete3Tip=["",spacer+"This option allows each member of the group to delete any records in the 'Whats (Tasks)' table."];

// whos table
whos_addTip=["",spacer+"This option allows all members of the group to add records to the 'Whos (Doers)' table. A member who adds a record to the table becomes the 'owner' of that record."];

whos_view0Tip=["",spacer+"This option prohibits all members of the group from viewing any record in the 'Whos (Doers)' table."];
whos_view1Tip=["",spacer+"This option allows each member of the group to view only his own records in the 'Whos (Doers)' table."];
whos_view2Tip=["",spacer+"This option allows each member of the group to view any record owned by any member of the group in the 'Whos (Doers)' table."];
whos_view3Tip=["",spacer+"This option allows each member of the group to view all records in the 'Whos (Doers)' table."];

whos_edit0Tip=["",spacer+"This option prohibits all members of the group from modifying any record in the 'Whos (Doers)' table."];
whos_edit1Tip=["",spacer+"This option allows each member of the group to edit only his own records in the 'Whos (Doers)' table."];
whos_edit2Tip=["",spacer+"This option allows each member of the group to edit any record owned by any member of the group in the 'Whos (Doers)' table."];
whos_edit3Tip=["",spacer+"This option allows each member of the group to edit any records in the 'Whos (Doers)' table, regardless of their owner."];

whos_delete0Tip=["",spacer+"This option prohibits all members of the group from deleting any record in the 'Whos (Doers)' table."];
whos_delete1Tip=["",spacer+"This option allows each member of the group to delete only his own records in the 'Whos (Doers)' table."];
whos_delete2Tip=["",spacer+"This option allows each member of the group to delete any record owned by any member of the group in the 'Whos (Doers)' table."];
whos_delete3Tip=["",spacer+"This option allows each member of the group to delete any records in the 'Whos (Doers)' table."];

// whens table
whens_addTip=["",spacer+"This option allows all members of the group to add records to the 'Whens (Frequency)' table. A member who adds a record to the table becomes the 'owner' of that record."];

whens_view0Tip=["",spacer+"This option prohibits all members of the group from viewing any record in the 'Whens (Frequency)' table."];
whens_view1Tip=["",spacer+"This option allows each member of the group to view only his own records in the 'Whens (Frequency)' table."];
whens_view2Tip=["",spacer+"This option allows each member of the group to view any record owned by any member of the group in the 'Whens (Frequency)' table."];
whens_view3Tip=["",spacer+"This option allows each member of the group to view all records in the 'Whens (Frequency)' table."];

whens_edit0Tip=["",spacer+"This option prohibits all members of the group from modifying any record in the 'Whens (Frequency)' table."];
whens_edit1Tip=["",spacer+"This option allows each member of the group to edit only his own records in the 'Whens (Frequency)' table."];
whens_edit2Tip=["",spacer+"This option allows each member of the group to edit any record owned by any member of the group in the 'Whens (Frequency)' table."];
whens_edit3Tip=["",spacer+"This option allows each member of the group to edit any records in the 'Whens (Frequency)' table, regardless of their owner."];

whens_delete0Tip=["",spacer+"This option prohibits all members of the group from deleting any record in the 'Whens (Frequency)' table."];
whens_delete1Tip=["",spacer+"This option allows each member of the group to delete only his own records in the 'Whens (Frequency)' table."];
whens_delete2Tip=["",spacer+"This option allows each member of the group to delete any record owned by any member of the group in the 'Whens (Frequency)' table."];
whens_delete3Tip=["",spacer+"This option allows each member of the group to delete any records in the 'Whens (Frequency)' table."];

// whichs table
whichs_addTip=["",spacer+"This option allows all members of the group to add records to the 'Whichs (Things)' table. A member who adds a record to the table becomes the 'owner' of that record."];

whichs_view0Tip=["",spacer+"This option prohibits all members of the group from viewing any record in the 'Whichs (Things)' table."];
whichs_view1Tip=["",spacer+"This option allows each member of the group to view only his own records in the 'Whichs (Things)' table."];
whichs_view2Tip=["",spacer+"This option allows each member of the group to view any record owned by any member of the group in the 'Whichs (Things)' table."];
whichs_view3Tip=["",spacer+"This option allows each member of the group to view all records in the 'Whichs (Things)' table."];

whichs_edit0Tip=["",spacer+"This option prohibits all members of the group from modifying any record in the 'Whichs (Things)' table."];
whichs_edit1Tip=["",spacer+"This option allows each member of the group to edit only his own records in the 'Whichs (Things)' table."];
whichs_edit2Tip=["",spacer+"This option allows each member of the group to edit any record owned by any member of the group in the 'Whichs (Things)' table."];
whichs_edit3Tip=["",spacer+"This option allows each member of the group to edit any records in the 'Whichs (Things)' table, regardless of their owner."];

whichs_delete0Tip=["",spacer+"This option prohibits all members of the group from deleting any record in the 'Whichs (Things)' table."];
whichs_delete1Tip=["",spacer+"This option allows each member of the group to delete only his own records in the 'Whichs (Things)' table."];
whichs_delete2Tip=["",spacer+"This option allows each member of the group to delete any record owned by any member of the group in the 'Whichs (Things)' table."];
whichs_delete3Tip=["",spacer+"This option allows each member of the group to delete any records in the 'Whichs (Things)' table."];

// wheres table
wheres_addTip=["",spacer+"This option allows all members of the group to add records to the 'Wheres (Area/Zone)' table. A member who adds a record to the table becomes the 'owner' of that record."];

wheres_view0Tip=["",spacer+"This option prohibits all members of the group from viewing any record in the 'Wheres (Area/Zone)' table."];
wheres_view1Tip=["",spacer+"This option allows each member of the group to view only his own records in the 'Wheres (Area/Zone)' table."];
wheres_view2Tip=["",spacer+"This option allows each member of the group to view any record owned by any member of the group in the 'Wheres (Area/Zone)' table."];
wheres_view3Tip=["",spacer+"This option allows each member of the group to view all records in the 'Wheres (Area/Zone)' table."];

wheres_edit0Tip=["",spacer+"This option prohibits all members of the group from modifying any record in the 'Wheres (Area/Zone)' table."];
wheres_edit1Tip=["",spacer+"This option allows each member of the group to edit only his own records in the 'Wheres (Area/Zone)' table."];
wheres_edit2Tip=["",spacer+"This option allows each member of the group to edit any record owned by any member of the group in the 'Wheres (Area/Zone)' table."];
wheres_edit3Tip=["",spacer+"This option allows each member of the group to edit any records in the 'Wheres (Area/Zone)' table, regardless of their owner."];

wheres_delete0Tip=["",spacer+"This option prohibits all members of the group from deleting any record in the 'Wheres (Area/Zone)' table."];
wheres_delete1Tip=["",spacer+"This option allows each member of the group to delete only his own records in the 'Wheres (Area/Zone)' table."];
wheres_delete2Tip=["",spacer+"This option allows each member of the group to delete any record owned by any member of the group in the 'Wheres (Area/Zone)' table."];
wheres_delete3Tip=["",spacer+"This option allows each member of the group to delete any records in the 'Wheres (Area/Zone)' table."];

// whys table
whys_addTip=["",spacer+"This option allows all members of the group to add records to the 'Whys (Precinct/Department)' table. A member who adds a record to the table becomes the 'owner' of that record."];

whys_view0Tip=["",spacer+"This option prohibits all members of the group from viewing any record in the 'Whys (Precinct/Department)' table."];
whys_view1Tip=["",spacer+"This option allows each member of the group to view only his own records in the 'Whys (Precinct/Department)' table."];
whys_view2Tip=["",spacer+"This option allows each member of the group to view any record owned by any member of the group in the 'Whys (Precinct/Department)' table."];
whys_view3Tip=["",spacer+"This option allows each member of the group to view all records in the 'Whys (Precinct/Department)' table."];

whys_edit0Tip=["",spacer+"This option prohibits all members of the group from modifying any record in the 'Whys (Precinct/Department)' table."];
whys_edit1Tip=["",spacer+"This option allows each member of the group to edit only his own records in the 'Whys (Precinct/Department)' table."];
whys_edit2Tip=["",spacer+"This option allows each member of the group to edit any record owned by any member of the group in the 'Whys (Precinct/Department)' table."];
whys_edit3Tip=["",spacer+"This option allows each member of the group to edit any records in the 'Whys (Precinct/Department)' table, regardless of their owner."];

whys_delete0Tip=["",spacer+"This option prohibits all members of the group from deleting any record in the 'Whys (Precinct/Department)' table."];
whys_delete1Tip=["",spacer+"This option allows each member of the group to delete only his own records in the 'Whys (Precinct/Department)' table."];
whys_delete2Tip=["",spacer+"This option allows each member of the group to delete any record owned by any member of the group in the 'Whys (Precinct/Department)' table."];
whys_delete3Tip=["",spacer+"This option allows each member of the group to delete any records in the 'Whys (Precinct/Department)' table."];

// howrs table
howrs_addTip=["",spacer+"This option allows all members of the group to add records to the 'How (R) (Method)' table. A member who adds a record to the table becomes the 'owner' of that record."];

howrs_view0Tip=["",spacer+"This option prohibits all members of the group from viewing any record in the 'How (R) (Method)' table."];
howrs_view1Tip=["",spacer+"This option allows each member of the group to view only his own records in the 'How (R) (Method)' table."];
howrs_view2Tip=["",spacer+"This option allows each member of the group to view any record owned by any member of the group in the 'How (R) (Method)' table."];
howrs_view3Tip=["",spacer+"This option allows each member of the group to view all records in the 'How (R) (Method)' table."];

howrs_edit0Tip=["",spacer+"This option prohibits all members of the group from modifying any record in the 'How (R) (Method)' table."];
howrs_edit1Tip=["",spacer+"This option allows each member of the group to edit only his own records in the 'How (R) (Method)' table."];
howrs_edit2Tip=["",spacer+"This option allows each member of the group to edit any record owned by any member of the group in the 'How (R) (Method)' table."];
howrs_edit3Tip=["",spacer+"This option allows each member of the group to edit any records in the 'How (R) (Method)' table, regardless of their owner."];

howrs_delete0Tip=["",spacer+"This option prohibits all members of the group from deleting any record in the 'How (R) (Method)' table."];
howrs_delete1Tip=["",spacer+"This option allows each member of the group to delete only his own records in the 'How (R) (Method)' table."];
howrs_delete2Tip=["",spacer+"This option allows each member of the group to delete any record owned by any member of the group in the 'How (R) (Method)' table."];
howrs_delete3Tip=["",spacer+"This option allows each member of the group to delete any records in the 'How (R) (Method)' table."];

// howss table
howss_addTip=["",spacer+"This option allows all members of the group to add records to the 'How (S) (Steps)' table. A member who adds a record to the table becomes the 'owner' of that record."];

howss_view0Tip=["",spacer+"This option prohibits all members of the group from viewing any record in the 'How (S) (Steps)' table."];
howss_view1Tip=["",spacer+"This option allows each member of the group to view only his own records in the 'How (S) (Steps)' table."];
howss_view2Tip=["",spacer+"This option allows each member of the group to view any record owned by any member of the group in the 'How (S) (Steps)' table."];
howss_view3Tip=["",spacer+"This option allows each member of the group to view all records in the 'How (S) (Steps)' table."];

howss_edit0Tip=["",spacer+"This option prohibits all members of the group from modifying any record in the 'How (S) (Steps)' table."];
howss_edit1Tip=["",spacer+"This option allows each member of the group to edit only his own records in the 'How (S) (Steps)' table."];
howss_edit2Tip=["",spacer+"This option allows each member of the group to edit any record owned by any member of the group in the 'How (S) (Steps)' table."];
howss_edit3Tip=["",spacer+"This option allows each member of the group to edit any records in the 'How (S) (Steps)' table, regardless of their owner."];

howss_delete0Tip=["",spacer+"This option prohibits all members of the group from deleting any record in the 'How (S) (Steps)' table."];
howss_delete1Tip=["",spacer+"This option allows each member of the group to delete only his own records in the 'How (S) (Steps)' table."];
howss_delete2Tip=["",spacer+"This option allows each member of the group to delete any record owned by any member of the group in the 'How (S) (Steps)' table."];
howss_delete3Tip=["",spacer+"This option allows each member of the group to delete any records in the 'How (S) (Steps)' table."];

// howqs table
howqs_addTip=["",spacer+"This option allows all members of the group to add records to the 'How (Q) (Quantify)' table. A member who adds a record to the table becomes the 'owner' of that record."];

howqs_view0Tip=["",spacer+"This option prohibits all members of the group from viewing any record in the 'How (Q) (Quantify)' table."];
howqs_view1Tip=["",spacer+"This option allows each member of the group to view only his own records in the 'How (Q) (Quantify)' table."];
howqs_view2Tip=["",spacer+"This option allows each member of the group to view any record owned by any member of the group in the 'How (Q) (Quantify)' table."];
howqs_view3Tip=["",spacer+"This option allows each member of the group to view all records in the 'How (Q) (Quantify)' table."];

howqs_edit0Tip=["",spacer+"This option prohibits all members of the group from modifying any record in the 'How (Q) (Quantify)' table."];
howqs_edit1Tip=["",spacer+"This option allows each member of the group to edit only his own records in the 'How (Q) (Quantify)' table."];
howqs_edit2Tip=["",spacer+"This option allows each member of the group to edit any record owned by any member of the group in the 'How (Q) (Quantify)' table."];
howqs_edit3Tip=["",spacer+"This option allows each member of the group to edit any records in the 'How (Q) (Quantify)' table, regardless of their owner."];

howqs_delete0Tip=["",spacer+"This option prohibits all members of the group from deleting any record in the 'How (Q) (Quantify)' table."];
howqs_delete1Tip=["",spacer+"This option allows each member of the group to delete only his own records in the 'How (Q) (Quantify)' table."];
howqs_delete2Tip=["",spacer+"This option allows each member of the group to delete any record owned by any member of the group in the 'How (Q) (Quantify)' table."];
howqs_delete3Tip=["",spacer+"This option allows each member of the group to delete any records in the 'How (Q) (Quantify)' table."];

// howts table
howts_addTip=["",spacer+"This option allows all members of the group to add records to the 'How (T) (Timespan)' table. A member who adds a record to the table becomes the 'owner' of that record."];

howts_view0Tip=["",spacer+"This option prohibits all members of the group from viewing any record in the 'How (T) (Timespan)' table."];
howts_view1Tip=["",spacer+"This option allows each member of the group to view only his own records in the 'How (T) (Timespan)' table."];
howts_view2Tip=["",spacer+"This option allows each member of the group to view any record owned by any member of the group in the 'How (T) (Timespan)' table."];
howts_view3Tip=["",spacer+"This option allows each member of the group to view all records in the 'How (T) (Timespan)' table."];

howts_edit0Tip=["",spacer+"This option prohibits all members of the group from modifying any record in the 'How (T) (Timespan)' table."];
howts_edit1Tip=["",spacer+"This option allows each member of the group to edit only his own records in the 'How (T) (Timespan)' table."];
howts_edit2Tip=["",spacer+"This option allows each member of the group to edit any record owned by any member of the group in the 'How (T) (Timespan)' table."];
howts_edit3Tip=["",spacer+"This option allows each member of the group to edit any records in the 'How (T) (Timespan)' table, regardless of their owner."];

howts_delete0Tip=["",spacer+"This option prohibits all members of the group from deleting any record in the 'How (T) (Timespan)' table."];
howts_delete1Tip=["",spacer+"This option allows each member of the group to delete only his own records in the 'How (T) (Timespan)' table."];
howts_delete2Tip=["",spacer+"This option allows each member of the group to delete any record owned by any member of the group in the 'How (T) (Timespan)' table."];
howts_delete3Tip=["",spacer+"This option allows each member of the group to delete any records in the 'How (T) (Timespan)' table."];

// utedb table
utedb_addTip=["",spacer+"This option allows all members of the group to add records to the 'Universal TEDB' table. A member who adds a record to the table becomes the 'owner' of that record."];

utedb_view0Tip=["",spacer+"This option prohibits all members of the group from viewing any record in the 'Universal TEDB' table."];
utedb_view1Tip=["",spacer+"This option allows each member of the group to view only his own records in the 'Universal TEDB' table."];
utedb_view2Tip=["",spacer+"This option allows each member of the group to view any record owned by any member of the group in the 'Universal TEDB' table."];
utedb_view3Tip=["",spacer+"This option allows each member of the group to view all records in the 'Universal TEDB' table."];

utedb_edit0Tip=["",spacer+"This option prohibits all members of the group from modifying any record in the 'Universal TEDB' table."];
utedb_edit1Tip=["",spacer+"This option allows each member of the group to edit only his own records in the 'Universal TEDB' table."];
utedb_edit2Tip=["",spacer+"This option allows each member of the group to edit any record owned by any member of the group in the 'Universal TEDB' table."];
utedb_edit3Tip=["",spacer+"This option allows each member of the group to edit any records in the 'Universal TEDB' table, regardless of their owner."];

utedb_delete0Tip=["",spacer+"This option prohibits all members of the group from deleting any record in the 'Universal TEDB' table."];
utedb_delete1Tip=["",spacer+"This option allows each member of the group to delete only his own records in the 'Universal TEDB' table."];
utedb_delete2Tip=["",spacer+"This option allows each member of the group to delete any record owned by any member of the group in the 'Universal TEDB' table."];
utedb_delete3Tip=["",spacer+"This option allows each member of the group to delete any records in the 'Universal TEDB' table."];

// premises table
premises_addTip=["",spacer+"This option allows all members of the group to add records to the 'Premises' table. A member who adds a record to the table becomes the 'owner' of that record."];

premises_view0Tip=["",spacer+"This option prohibits all members of the group from viewing any record in the 'Premises' table."];
premises_view1Tip=["",spacer+"This option allows each member of the group to view only his own records in the 'Premises' table."];
premises_view2Tip=["",spacer+"This option allows each member of the group to view any record owned by any member of the group in the 'Premises' table."];
premises_view3Tip=["",spacer+"This option allows each member of the group to view all records in the 'Premises' table."];

premises_edit0Tip=["",spacer+"This option prohibits all members of the group from modifying any record in the 'Premises' table."];
premises_edit1Tip=["",spacer+"This option allows each member of the group to edit only his own records in the 'Premises' table."];
premises_edit2Tip=["",spacer+"This option allows each member of the group to edit any record owned by any member of the group in the 'Premises' table."];
premises_edit3Tip=["",spacer+"This option allows each member of the group to edit any records in the 'Premises' table, regardless of their owner."];

premises_delete0Tip=["",spacer+"This option prohibits all members of the group from deleting any record in the 'Premises' table."];
premises_delete1Tip=["",spacer+"This option allows each member of the group to delete only his own records in the 'Premises' table."];
premises_delete2Tip=["",spacer+"This option allows each member of the group to delete any record owned by any member of the group in the 'Premises' table."];
premises_delete3Tip=["",spacer+"This option allows each member of the group to delete any records in the 'Premises' table."];

// pnb table
pnb_addTip=["",spacer+"This option allows all members of the group to add records to the 'Presence & Being' table. A member who adds a record to the table becomes the 'owner' of that record."];

pnb_view0Tip=["",spacer+"This option prohibits all members of the group from viewing any record in the 'Presence & Being' table."];
pnb_view1Tip=["",spacer+"This option allows each member of the group to view only his own records in the 'Presence & Being' table."];
pnb_view2Tip=["",spacer+"This option allows each member of the group to view any record owned by any member of the group in the 'Presence & Being' table."];
pnb_view3Tip=["",spacer+"This option allows each member of the group to view all records in the 'Presence & Being' table."];

pnb_edit0Tip=["",spacer+"This option prohibits all members of the group from modifying any record in the 'Presence & Being' table."];
pnb_edit1Tip=["",spacer+"This option allows each member of the group to edit only his own records in the 'Presence & Being' table."];
pnb_edit2Tip=["",spacer+"This option allows each member of the group to edit any record owned by any member of the group in the 'Presence & Being' table."];
pnb_edit3Tip=["",spacer+"This option allows each member of the group to edit any records in the 'Presence & Being' table, regardless of their owner."];

pnb_delete0Tip=["",spacer+"This option prohibits all members of the group from deleting any record in the 'Presence & Being' table."];
pnb_delete1Tip=["",spacer+"This option allows each member of the group to delete only his own records in the 'Presence & Being' table."];
pnb_delete2Tip=["",spacer+"This option allows each member of the group to delete any record owned by any member of the group in the 'Presence & Being' table."];
pnb_delete3Tip=["",spacer+"This option allows each member of the group to delete any records in the 'Presence & Being' table."];

// ilct_info table
ilct_info_addTip=["",spacer+"This option allows all members of the group to add records to the 'IMPORTANT LCT INFO' table. A member who adds a record to the table becomes the 'owner' of that record."];

ilct_info_view0Tip=["",spacer+"This option prohibits all members of the group from viewing any record in the 'IMPORTANT LCT INFO' table."];
ilct_info_view1Tip=["",spacer+"This option allows each member of the group to view only his own records in the 'IMPORTANT LCT INFO' table."];
ilct_info_view2Tip=["",spacer+"This option allows each member of the group to view any record owned by any member of the group in the 'IMPORTANT LCT INFO' table."];
ilct_info_view3Tip=["",spacer+"This option allows each member of the group to view all records in the 'IMPORTANT LCT INFO' table."];

ilct_info_edit0Tip=["",spacer+"This option prohibits all members of the group from modifying any record in the 'IMPORTANT LCT INFO' table."];
ilct_info_edit1Tip=["",spacer+"This option allows each member of the group to edit only his own records in the 'IMPORTANT LCT INFO' table."];
ilct_info_edit2Tip=["",spacer+"This option allows each member of the group to edit any record owned by any member of the group in the 'IMPORTANT LCT INFO' table."];
ilct_info_edit3Tip=["",spacer+"This option allows each member of the group to edit any records in the 'IMPORTANT LCT INFO' table, regardless of their owner."];

ilct_info_delete0Tip=["",spacer+"This option prohibits all members of the group from deleting any record in the 'IMPORTANT LCT INFO' table."];
ilct_info_delete1Tip=["",spacer+"This option allows each member of the group to delete only his own records in the 'IMPORTANT LCT INFO' table."];
ilct_info_delete2Tip=["",spacer+"This option allows each member of the group to delete any record owned by any member of the group in the 'IMPORTANT LCT INFO' table."];
ilct_info_delete3Tip=["",spacer+"This option allows each member of the group to delete any records in the 'IMPORTANT LCT INFO' table."];

/*
	Style syntax:
	-------------
	[TitleColor,TextColor,TitleBgColor,TextBgColor,TitleBgImag,TextBgImag,TitleTextAlign,
	TextTextAlign,TitleFontFace,TextFontFace, TipPosition, StickyStyle, TitleFontSize,
	TextFontSize, Width, Height, BorderSize, PadTextArea, CoordinateX , CoordinateY,
	TransitionNumber, TransitionDuration, TransparencyLevel ,ShadowType, ShadowColor]

*/

toolTipStyle=["white","#00008B","#000099","#E6E6FA","","images/helpBg.gif","","","","\"Trebuchet MS\", sans-serif","","","","3",400,"",1,2,10,10,51,1,0,"",""];

applyCssFilter();
