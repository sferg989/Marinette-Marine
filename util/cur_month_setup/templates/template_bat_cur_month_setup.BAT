[Process001]

ProcessID=CopyProject

CopyFrom=####

CopyTo=****

CopyData=1

DeleteTarget=0

CopyCalendar=1

NewDescription=^^^^


[Process002]

ProcessID=BackupProject

Project=####

Destination=&&&&

IncludeAncillaryFiles=0

ExcludeLinkFiles=0


[Process003]

ProcessID=BackupProject

Project=####

Destination=(((((

IncludeAncillaryFiles=0

ExcludeLinkFiles=0

[Process004]
ProcessID=Reclass
Project=####
SourceAction=Copy
SourceClass=Actual
TargetAction=Replace
TargetClass=FF
Criteria=Total Project
IncludeChildren=1
Scope=All
AllowComplete=1

[Process005]
ProcessID=Reclass
Project=####
SourceAction=Copy
SourceClass=CA
TargetAction=Add
TargetClass=FF
Criteria=Total Project
IncludeChildren=1
Scope=All
AllowComplete=1

[Process006]
ProcessID=Reclass
Project=####
SourceAction=Copy
SourceClass=EA
TargetAction=Add
TargetClass=FF
Criteria=Total Project
IncludeChildren=1
Scope=All
AllowComplete=1

[Process007]
ProcessID=Reclass
Project=####
SourceAction=Copy
SourceClass=Forecast
TargetAction=Add
TargetClass=FF
Criteria=Total Project
IncludeChildren=1
Scope=All
AllowComplete=1

[Process008]
ProcessID=Reclass
Project=####
SourceAction=Copy
SourceClass=Budget
TargetAction=Replace
TargetClass=PB
Criteria=Total Project
IncludeChildren=1
Scope=All
AllowComplete=1

[Process009]
ProcessID=Reclass
Project=####
SourceAction=Copy
SourceClass=Actual
TargetAction=Replace
TargetClass=PA
Criteria=Total Project
IncludeChildren=1
Scope=All
AllowComplete=1

[Process010]
ProcessID=Reclass
Project=####
SourceAction=Copy
SourceClass=EA
TargetAction=Replace
TargetClass=PE
Criteria=Total Project
IncludeChildren=1
Scope=All
AllowComplete=1

[Process011]
ProcessID=Reclass
Project=####
SourceAction=Copy
SourceClass=Forecast
TargetAction=Replace
TargetClass=PF
Criteria=Total Project
IncludeChildren=1
Scope=All
AllowComplete=1

[Process012]
ProcessID=Reclass
Project=####
SourceAction=Copy
SourceClass=RA
TargetAction=Add
TargetClass=PB
Criteria=Total Project
IncludeChildren=1
Scope=All
AllowComplete=1

[Process013]
ProcessID=Reclass
Project=####
SourceAction=Copy
SourceClass=CA
TargetAction=Replace
TargetClass=PCA
Criteria=Total Project
IncludeChildren=1
Scope=All
AllowComplete=1

[Process014]

ProcessID=BatchReport

BatchReport=#### Class Check

[Process015]

ProcessID=BackupProject

Project=####

Destination=)))))

IncludeAncillaryFiles=0

ExcludeLinkFiles=0


[Process016]
ProcessID=AdvanceCalendar
Project=####
UseStatusDateAsActualStartDateForLoE=0
PeriodsPriorToStatusDate=0
PeriodsFollowingStatusDate=0
UpdateRateSetsUsedWithFTE=0
SynchCalendarWithProjectStatus=1
SynchProjectWithCalendarStatus=0
SkipRollingWave=1
UpdateEAC=0


[Process017]

ProcessID=BackupProject

Project=####

Destination=+++++

IncludeAncillaryFiles=0

ExcludeLinkFiles=0

[Process018]

ProcessID=SQLCommand

SQLCommand=%%%%

