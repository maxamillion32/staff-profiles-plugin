Staff Profiles Plugin
=====================

This plugin creates a new custom post type (people) whose main function is to display the content of custom user data fields which are edited by users in their Wordpress profiles. This therefore serves to allow members of staff to edit their own profile pages without the need for them to be given access to edit other parts of the website.

When the plugin is enabled for a site, the user profile fields are altered to include the following data entry fields:

<dl>
	<dt>Title, First Name and Last Name</dt>
	<dd>These fields map directly to the corresponding fields for the standard Wordpress user.</dd>
	<dt>Telephone</dt>
	<dd>Office phone number in the form 0113 123 4567</dd>
	<dt>Twitter</dt>
	<dd>Twitter username - this will be used to make a link to the users' twitter page</dd>
	<dt>Location</dt>
	<dd>Office building and room number (e.g. Clothworkers' Building South, Room 1.02)</dd>
	<dt>Office Hours</dt>
	<dd>Regular office hours (e.g. Monday - Thursday, 1.00pm. - 4.00pm.)</dd>
	<dt>Position</dt>
	<dd>Formal job title(s) e.g. Senior Lecturer in International Communications</dd>
	<dt>Qualifications</dt>
	<dd>Academic qualifications</dd>
	<dt>Biography Summary</dt>
	<dd>A very brief summary of the staff member's role including areas of expertise and research interests. This is used on the people listing page</dd>
	<dt>Biographical Info</dt>
	<dd>A longer biography (150 - 250 words) - featured on the main profile page</dd>
	<dt>Research Interests</dt>
	<dd>Details of research interests - as a series of bullet points followed by a paragraph giving further detail if required</dd>
	<dt>Teaching</dt>
	<dd>Details of the modules the staff member teaches / coordinates along with any other teaching responsibilities</dd>
	<dt>Departmental Responsibilities</dt>
	<dd>Any additional responsibilities the staff member has in their department (e.g. Director of Research, Exams Officer etc.)</dd>
	<dt>Publications & Research Outputs</dt>
	<dd>This is a field for the six digit payroll ID (e.g. 901234) to retrieve their publications feed from Symplectic. The following checkbox options relate to the display of items returned in that feed:
	<ul>
		<li>Include publications <em>with no status set</em> in the list (symplectic sometimes omits the publication status on items)</li>
		<li>Include 'Accepted' publications in the list</li>
		<li>Include 'Submitted' publications in the list</li>
		<li>Include 'In Preparation' publications in the list</li>
		<li>Include 'Unpublished' publications in the list</li>
		<li>Include the <strong>abstract</strong> field in your  publications list - this will display a link to show the abstract using an expando</li>
		<li>nclude the <strong>notes</strong> field in the publications list</li>
		<li>Include the <strong>author url</strong> field in the publications list (this is an arbitrary URL which you can supply, and can be used for web-based resources or links to copies of the publication held on other sites)</li>
		<li>Include the <strong>repository url</strong> field in your  publications list (this will include a link to the White Rose repository copy of the publication (if available)</li>
	</ul>
	</dd>
	<dt>Research Projects & Grants</dt>
	<dd>Details of research projects and awards (past, current and proposed)</dd>
	<dt>Research Centres & Groups</dt>
	<dd>Details of any research centres / groups of which you are a member.</dd>
	<dt>External Appointments</dt>
	<dd>e.g. External examiner, editor, member / chair of advisory boards, conference organising committees etc.</dd>
	<dt>PhD & Postdoctoral Supervision</dt>
	<dd>A list of current and past PhD and postdoctoral supervisees and the topic of their research</dd>
	<dt>PhD Thesis</dt>
	<dd>The abstract of the staff member's PhD thesis and a link to the full text if available</dd>
	<dt>Professional Practice</dt>
	<dd>e.g. exhibitions, performances, compositions, media projects etc.</dd>
	<dt>Links</dt>
	<dd>Links to any websites related to their work which may be of interest to visitors</dd>
</dl>

Plugin options
--------------

You can mass edit users on the plugin options page (in Users->Staff Profiles), as well as set a default sort order for publication types. The fields available here are the symplectic ID, and the assignment of staff categories (which will present themselves as tabs on the People page).

Ordering Publication types
--------------------------

Publication types can be put in any order by site administrators using a drag and drop tool on the user profile - the default order for publication types is set on the main options page for the plugin (see above)