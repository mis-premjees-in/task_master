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

// whys table
whys_addTip=["",spacer+"This option allows all members of the group to add records to the 'Whys' table. A member who adds a record to the table becomes the 'owner' of that record."];

whys_view0Tip=["",spacer+"This option prohibits all members of the group from viewing any record in the 'Whys' table."];
whys_view1Tip=["",spacer+"This option allows each member of the group to view only his own records in the 'Whys' table."];
whys_view2Tip=["",spacer+"This option allows each member of the group to view any record owned by any member of the group in the 'Whys' table."];
whys_view3Tip=["",spacer+"This option allows each member of the group to view all records in the 'Whys' table."];

whys_edit0Tip=["",spacer+"This option prohibits all members of the group from modifying any record in the 'Whys' table."];
whys_edit1Tip=["",spacer+"This option allows each member of the group to edit only his own records in the 'Whys' table."];
whys_edit2Tip=["",spacer+"This option allows each member of the group to edit any record owned by any member of the group in the 'Whys' table."];
whys_edit3Tip=["",spacer+"This option allows each member of the group to edit any records in the 'Whys' table, regardless of their owner."];

whys_delete0Tip=["",spacer+"This option prohibits all members of the group from deleting any record in the 'Whys' table."];
whys_delete1Tip=["",spacer+"This option allows each member of the group to delete only his own records in the 'Whys' table."];
whys_delete2Tip=["",spacer+"This option allows each member of the group to delete any record owned by any member of the group in the 'Whys' table."];
whys_delete3Tip=["",spacer+"This option allows each member of the group to delete any records in the 'Whys' table."];

// wheres table
wheres_addTip=["",spacer+"This option allows all members of the group to add records to the 'Wheres' table. A member who adds a record to the table becomes the 'owner' of that record."];

wheres_view0Tip=["",spacer+"This option prohibits all members of the group from viewing any record in the 'Wheres' table."];
wheres_view1Tip=["",spacer+"This option allows each member of the group to view only his own records in the 'Wheres' table."];
wheres_view2Tip=["",spacer+"This option allows each member of the group to view any record owned by any member of the group in the 'Wheres' table."];
wheres_view3Tip=["",spacer+"This option allows each member of the group to view all records in the 'Wheres' table."];

wheres_edit0Tip=["",spacer+"This option prohibits all members of the group from modifying any record in the 'Wheres' table."];
wheres_edit1Tip=["",spacer+"This option allows each member of the group to edit only his own records in the 'Wheres' table."];
wheres_edit2Tip=["",spacer+"This option allows each member of the group to edit any record owned by any member of the group in the 'Wheres' table."];
wheres_edit3Tip=["",spacer+"This option allows each member of the group to edit any records in the 'Wheres' table, regardless of their owner."];

wheres_delete0Tip=["",spacer+"This option prohibits all members of the group from deleting any record in the 'Wheres' table."];
wheres_delete1Tip=["",spacer+"This option allows each member of the group to delete only his own records in the 'Wheres' table."];
wheres_delete2Tip=["",spacer+"This option allows each member of the group to delete any record owned by any member of the group in the 'Wheres' table."];
wheres_delete3Tip=["",spacer+"This option allows each member of the group to delete any records in the 'Wheres' table."];

// whichs table
whichs_addTip=["",spacer+"This option allows all members of the group to add records to the 'Whichs' table. A member who adds a record to the table becomes the 'owner' of that record."];

whichs_view0Tip=["",spacer+"This option prohibits all members of the group from viewing any record in the 'Whichs' table."];
whichs_view1Tip=["",spacer+"This option allows each member of the group to view only his own records in the 'Whichs' table."];
whichs_view2Tip=["",spacer+"This option allows each member of the group to view any record owned by any member of the group in the 'Whichs' table."];
whichs_view3Tip=["",spacer+"This option allows each member of the group to view all records in the 'Whichs' table."];

whichs_edit0Tip=["",spacer+"This option prohibits all members of the group from modifying any record in the 'Whichs' table."];
whichs_edit1Tip=["",spacer+"This option allows each member of the group to edit only his own records in the 'Whichs' table."];
whichs_edit2Tip=["",spacer+"This option allows each member of the group to edit any record owned by any member of the group in the 'Whichs' table."];
whichs_edit3Tip=["",spacer+"This option allows each member of the group to edit any records in the 'Whichs' table, regardless of their owner."];

whichs_delete0Tip=["",spacer+"This option prohibits all members of the group from deleting any record in the 'Whichs' table."];
whichs_delete1Tip=["",spacer+"This option allows each member of the group to delete only his own records in the 'Whichs' table."];
whichs_delete2Tip=["",spacer+"This option allows each member of the group to delete any record owned by any member of the group in the 'Whichs' table."];
whichs_delete3Tip=["",spacer+"This option allows each member of the group to delete any records in the 'Whichs' table."];

// whens table
whens_addTip=["",spacer+"This option allows all members of the group to add records to the 'Whens' table. A member who adds a record to the table becomes the 'owner' of that record."];

whens_view0Tip=["",spacer+"This option prohibits all members of the group from viewing any record in the 'Whens' table."];
whens_view1Tip=["",spacer+"This option allows each member of the group to view only his own records in the 'Whens' table."];
whens_view2Tip=["",spacer+"This option allows each member of the group to view any record owned by any member of the group in the 'Whens' table."];
whens_view3Tip=["",spacer+"This option allows each member of the group to view all records in the 'Whens' table."];

whens_edit0Tip=["",spacer+"This option prohibits all members of the group from modifying any record in the 'Whens' table."];
whens_edit1Tip=["",spacer+"This option allows each member of the group to edit only his own records in the 'Whens' table."];
whens_edit2Tip=["",spacer+"This option allows each member of the group to edit any record owned by any member of the group in the 'Whens' table."];
whens_edit3Tip=["",spacer+"This option allows each member of the group to edit any records in the 'Whens' table, regardless of their owner."];

whens_delete0Tip=["",spacer+"This option prohibits all members of the group from deleting any record in the 'Whens' table."];
whens_delete1Tip=["",spacer+"This option allows each member of the group to delete only his own records in the 'Whens' table."];
whens_delete2Tip=["",spacer+"This option allows each member of the group to delete any record owned by any member of the group in the 'Whens' table."];
whens_delete3Tip=["",spacer+"This option allows each member of the group to delete any records in the 'Whens' table."];

// whos table
whos_addTip=["",spacer+"This option allows all members of the group to add records to the 'Whos' table. A member who adds a record to the table becomes the 'owner' of that record."];

whos_view0Tip=["",spacer+"This option prohibits all members of the group from viewing any record in the 'Whos' table."];
whos_view1Tip=["",spacer+"This option allows each member of the group to view only his own records in the 'Whos' table."];
whos_view2Tip=["",spacer+"This option allows each member of the group to view any record owned by any member of the group in the 'Whos' table."];
whos_view3Tip=["",spacer+"This option allows each member of the group to view all records in the 'Whos' table."];

whos_edit0Tip=["",spacer+"This option prohibits all members of the group from modifying any record in the 'Whos' table."];
whos_edit1Tip=["",spacer+"This option allows each member of the group to edit only his own records in the 'Whos' table."];
whos_edit2Tip=["",spacer+"This option allows each member of the group to edit any record owned by any member of the group in the 'Whos' table."];
whos_edit3Tip=["",spacer+"This option allows each member of the group to edit any records in the 'Whos' table, regardless of their owner."];

whos_delete0Tip=["",spacer+"This option prohibits all members of the group from deleting any record in the 'Whos' table."];
whos_delete1Tip=["",spacer+"This option allows each member of the group to delete only his own records in the 'Whos' table."];
whos_delete2Tip=["",spacer+"This option allows each member of the group to delete any record owned by any member of the group in the 'Whos' table."];
whos_delete3Tip=["",spacer+"This option allows each member of the group to delete any records in the 'Whos' table."];

// whats table
whats_addTip=["",spacer+"This option allows all members of the group to add records to the 'Whats' table. A member who adds a record to the table becomes the 'owner' of that record."];

whats_view0Tip=["",spacer+"This option prohibits all members of the group from viewing any record in the 'Whats' table."];
whats_view1Tip=["",spacer+"This option allows each member of the group to view only his own records in the 'Whats' table."];
whats_view2Tip=["",spacer+"This option allows each member of the group to view any record owned by any member of the group in the 'Whats' table."];
whats_view3Tip=["",spacer+"This option allows each member of the group to view all records in the 'Whats' table."];

whats_edit0Tip=["",spacer+"This option prohibits all members of the group from modifying any record in the 'Whats' table."];
whats_edit1Tip=["",spacer+"This option allows each member of the group to edit only his own records in the 'Whats' table."];
whats_edit2Tip=["",spacer+"This option allows each member of the group to edit any record owned by any member of the group in the 'Whats' table."];
whats_edit3Tip=["",spacer+"This option allows each member of the group to edit any records in the 'Whats' table, regardless of their owner."];

whats_delete0Tip=["",spacer+"This option prohibits all members of the group from deleting any record in the 'Whats' table."];
whats_delete1Tip=["",spacer+"This option allows each member of the group to delete only his own records in the 'Whats' table."];
whats_delete2Tip=["",spacer+"This option allows each member of the group to delete any record owned by any member of the group in the 'Whats' table."];
whats_delete3Tip=["",spacer+"This option allows each member of the group to delete any records in the 'Whats' table."];

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
