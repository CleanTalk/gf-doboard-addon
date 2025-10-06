=== CleanTalk doBoard Add-On for Gravity Forms ===
Contributors: anton_cleantalk, cleantalk, glomberg, alexandergull, sergefcleantalk
Donate link: https://doboard.com/
Tags: gravityforms, doboard, forms, automation, project management
Requires at least: 5.6
Tested up to: 6.8
Stable tag: 1.0.2
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Seamlessly integrate Gravity Forms with doBoard — automatically create tasks for every form submission!

== Description ==

The Gravity Forms doBoard Add-On allows you to automatically create tasks in the doBoard project management system whenever a Gravity Forms form is submitted.
Perfect for automating the processing of leads, orders, support requests, or any other data collected via forms on your website.

= doBoard Integration for Gravity Forms =

Direct integration between Gravity Forms and doBoard:
* Automated task creation from submitted forms
* Organization of project contact information
* Team collaboration features for sales and support teams
* A simple alternative to heavyweight CRM platforms

= Turn form submissions into organized workflows =

Link Gravity Forms with doBoard to automatically transform every submission into a structured task. If you're managing customer inquiries, processing service requests, or collecting lead information, this integration ensures nothing goes unnoticed.

= Automatic Workflow Creation =

Every form entry goes directly into your doBoard workspace, creating tasks that can be assigned, tracked, and completed. No manual data transfer, no lost submissions—just a clear process from form to action.
Simplified CRM Capabilities
doBoard functions as a practical contact management system for teams who need CRM capabilities without the need for enterprise features:
* Store contact information from submitted forms in organized projects
* Track customer interactions through comments and task updates
* Segment contacts by assigning them to specific projects or workflows
* Track interaction history and follow-up status in a single dashboard

= Simplified Lead Management =

Sales and support teams can systematically manage incoming leads. Each submission becomes a trackable task with attached contact information, making it easy to assign responsibility, set follow-up dates, and track conversion progress.

= Easy Process Automation =

Create repeatable workflows for common business processes—quote requests, support tickets, partnership inquiries—by automatically routing form data to the appropriate team members. doBoard organizes all processes while your team focuses on execution.

= What you get =

Ideal for small and medium teams managing customer relationships, service requests, or sales funnels through WordPress.

== Installation ==

* Upload the plugin files to the /wp-content/plugins/cleantalk-doboard-add-on-for-gravity-forms directory, or install the plugin through the WordPress plugins screen directly.
* Activate the plugin through the 'Plugins' screen in WordPress.
* Go to Gravity Forms Settings → doBoard and enter your doBoard user token (global setting).
* For each form, go to Forms → Form Settings → doBoard Feeds and configure the integration options (account, project, board, labels, etc.).
* Save your settings. Tasks will now be created automatically on form submission according to your feed configuration.

== Frequently Asked Questions ==

= What do I need to use this plugin? =
You'll need:

>Gravity Forms 2.5 or higher
>An active account at <a href="https://doboard.com/">doBoard</a>

= Where do I get my doBoard user token? =
You can find your token in your doBoard account settings under API section.

== Screenshots ==
1. Plugin settings page.
2. Feed configuration example.
3. Task created in doBoard.

== Changelog ==

= 1.0.2 =
* Upd. Implemented changes required by the WordPress review team..
* Fix. Exceptions on task/comment adding when the doBoard license is inactive.

= 1.0.1 =

Docs and readme.txt added.

= 1.0.0 =

Initial release


== Support ==

If you have questions or suggestions, contact us at welcome@cleantalk.org.

== External services ==

This plugin integrates with api.doboard.com service. By using this plugin, you agree to <a href="https://doboard.com/terms-of-use/">terms</a> and <a href="https://doboard.com/privacy-policy/">privacy policy</a> of doBoard service.

Cases of data transmission to this service:

1. User Authentication
Data Sent: Only the user_token as a URL query parameter.

2. Fetching Projects
Data Sent: The account_id (in the path) and session_id (as a URL query parameter).

3. Fetching Task Boards (Tracks)
Data Sent: The account_id (path), session_id, the fixed parameter status=ACTIVE, and an optional project_id (all as URL query parameters).

4. Fetching Labels
Data Sent: The account_id (path) and session_id (as a URL query parameter).

5. Adding a Task
Data Sent: The account_id (in the path). The task data (an array of properties like title, description, etc.) is sent in the body of the POST request, which is encoded as JSON.

6. Adding a Comment
Data Sent: The account_id (in the path). The comment data (e.g., task_id, message, etc.) is sent in the body of the POST request, which is encoded as JSON.
