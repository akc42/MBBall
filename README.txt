mbball is an American Football results picking competition Manager.
Used on Melinda's Backups (http://www.melindasbackups.com) for their
annual competition during the American Football season, this software
provides two parts.

An administrator can manage the competition, defining the matches and
their cut-off times for picking results and then subsequently entering
the match results.

Players sign on to the football (using the their community forum login
- currently uses SMF)

All results have stored in a Postgres Database, that can hold multiple
competitions over several years.

This branch has been created for two separate changes.

Firstly, we will port the program to use the Yii Framework.  This is
expected to bring in benefits in the overall separation of concerns,
by using an MVC architecture, but I also believe I can better
generalise the use of themes, and, perhaps more importantly I can be a
tiny bit cleverer in caching results.  At the moment the main display
for a user when there is a full competitiion with its results to
display uses over 115 separate database queries.  Nearly all of that
data can be cached globally, and the remaining small part of it cached
on a per user basis.

Because of the caching refered to above, I also believe I can port the
software to use sqlite as the database behind this.  I did a trial
with sqlite before (with separate databases per competition - see the
sqlite_separate_db branch) and that was slightly slower than with the
Postgresql database (currently used on the Master Branch). Although it
is fast (and better suited because of its transaction management), I
believe that the sqlite version is a lot easier to manage the
database. So far the database containing 4 years worth of competition
makes a sqlite database of about 2MB, so its not massive amounts of
data we are taking about here.

One benefit of using Yii, is that if sqlite doesn't work, it should
not be too hard to back convert it to Postgres.

The last thing to add is that the entire project is now within an
Eclipse project.  I am not sure the directory structure is right for
that, but we will see.

