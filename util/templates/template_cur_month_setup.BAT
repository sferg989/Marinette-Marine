[Process001]

ProcessID=CopyProject

CopyFrom=0471-

CopyTo=04710917

CopyData=1

DeleteTarget=0

CopyCalendar=1

NewDescription=0471 Sep 2017 Archive


[Process002]

ProcessID=BackupProject

Project=0469

Destination=Y:/Program Management/cobra processing/LCS9/0469/0469 2017/0469 05.17 Cobra Processing/0469 May 2017 Cobra Backups

IncludeAncillaryFiles=0

ExcludeLinkFiles=0

[Process003]

ProcessID=BackupProject

Project=0469

Destination=Y:/Program Management/cobra processing/LCS9/0469/0469 2017/0469 05.17 Cobra Processing/0469 May 2017 Cobra Backups

IncludeAncillaryFiles=0

ExcludeLinkFiles=0

[Process001]
ProcessID=Reclass
Project=0477
SourceAction=Copy
SourceClass=Actual
TargetAction=Replace
TargetClass=FF
Criteria=Total Project
IncludeChildren=1
Scope=All
AllowComplete=1

[Process002]
ProcessID=Reclass
Project=0477
SourceAction=Copy
SourceClass=CA
TargetAction=Add
TargetClass=FF
Criteria=Total Project
IncludeChildren=1
Scope=All
AllowComplete=1

[Process003]
ProcessID=Reclass
Project=0477
SourceAction=Copy
SourceClass=EA
TargetAction=Add
TargetClass=FF
Criteria=Total Project
IncludeChildren=1
Scope=All
AllowComplete=1

[Process004]
ProcessID=Reclass
Project=0477
SourceAction=Copy
SourceClass=Forecast
TargetAction=Add
TargetClass=FF
Criteria=Total Project
IncludeChildren=1
Scope=All
AllowComplete=1

[Process005]
ProcessID=Reclass
Project=0477
SourceAction=Copy
SourceClass=Budget
TargetAction=Replace
TargetClass=PB
Criteria=Total Project
IncludeChildren=1
Scope=All
AllowComplete=1

[Process006]
ProcessID=Reclass
Project=0477
SourceAction=Copy
SourceClass=Actual
TargetAction=Replace
TargetClass=PA
Criteria=Total Project
IncludeChildren=1
Scope=All
AllowComplete=1

[Process007]
ProcessID=Reclass
Project=0477
SourceAction=Copy
SourceClass=EA
TargetAction=Replace
TargetClass=PE
Criteria=Total Project
IncludeChildren=1
Scope=All
AllowComplete=1

[Process008]
ProcessID=Reclass
Project=0477
SourceAction=Copy
SourceClass=Forecast
TargetAction=Replace
TargetClass=PF
Criteria=Total Project
IncludeChildren=1
Scope=All
AllowComplete=1

[Process009]
ProcessID=Reclass
Project=0477
SourceAction=Copy
SourceClass=RA
TargetAction=Add
TargetClass=PB
Criteria=Total Project
IncludeChildren=1
Scope=All
AllowComplete=1

[Process010]
ProcessID=Reclass
Project=0477
SourceAction=Copy
SourceClass=CA
TargetAction=Replace
TargetClass=PCA
Criteria=Total Project
IncludeChildren=1
Scope=All
AllowComplete=1

[Process003]

ProcessID=BatchReport

BatchReport=csv0477StatusValid

[Process003]

ProcessID=BackupProject

Project=0469

Destination=Y:/Program Management/cobra processing/LCS9/0469/0469 2017/0469 05.17 Cobra Processing/0469 May 2017 Cobra Backups

IncludeAncillaryFiles=0

ExcludeLinkFiles=0


[Process001]
ProcessID=AdvanceCalendar
Project=****
UseStatusDateAsActualStartDateForLoE=0
PeriodsPriorToStatusDate=0
PeriodsFollowingStatusDate=0
UpdateRateSetsUsedWithFTE=0
SynchCalendarWithProjectStatus=1
SynchProjectWithCalendarStatus=0
SkipRollingWave=1
UpdateEAC=0


[Process003]

ProcessID=BackupProject

Project=0469

Destination=Y:/Program Management/cobra processing/LCS9/0469/0469 2017/0469 05.17 Cobra Processing/0469 May 2017 Cobra Backups

IncludeAncillaryFiles=0

ExcludeLinkFiles=0

[Process021]

ProcessID=SQLCommand

SQLCommand=update  FISCDETL  set  FIELD06 = '', FLAG06 = ''  where  FISCFILE = '0473' and FIELD06 in ('PLAN 3','PLAN 2','PLAN 1','FREEZE','THISMONTH','TODATE');update  FISCDETL  set  FIELD06 = 'PLAN 3',  FLAG06 = '*' where  FISCFILE = '0473' and FSC_DATE  = '2018-04- 00:00:00.000';update  FISCDETL  set  FIELD06 = 'PLAN 2',  FLAG06 = '*' where  FISCFILE = '0473' and FSC_DATE  = '2018-03- 00:00:00.000';update  FISCDETL  set  FIELD06 = 'PLAN 1',  FLAG06 = '*' where  FISCFILE = '0473' and FSC_DATE  = '2018-02- 00:00:00.000';update  FISCDETL  set  FIELD06 = 'FREEZE',  FLAG06 = '*' where  FISCFILE = '0473' and FSC_DATE  = '2018-01- 00:00:00.000';update  FISCDETL  set  FIELD06 = 'THISMONTH',  FLAG06 = '*' where  FISCFILE = '0473' and FSC_DATE  = '2017-12-30 00:00:00.000';update  FISCDETL  set  FIELD06 = 'TODATE',  FLAG06 = '*' where  FISCFILE = '0473' and FSC_DATE  = '2017-11-25 00:00:00.000';update  FISCDETL  set  FIELD18 = '', FLAG18 = ''  where  FISCFILE = '0473' and FIELD18 in ('PREVIOUS','TODATE');update  FISCDETL  set  FIELD18 = 'PREVIOUS',  FLAG18 = '*' where  FISCFILE = '0473' and FSC_DATE  = '2017-11-25 00:00:00.000';update  FISCDETL  set  FIELD18 = 'TODATE',  FLAG18 = '*' where  FISCFILE = '0473' and FSC_DATE  = '2017-12-30 00:00:00.000';update  FISCDETL  set  FIELD19 = '', FLAG19 = ''  where  FISCFILE = '0473' and FLAG19 = '*';update  FISCDETL  set  FIELD19 = 'PREVIOUS',  FLAG19 = '*' where  FISCFILE = '0473' and FSC_DATE  = '2017-11-25 00:00:00.000';update  FISCDETL  set  FIELD19 = 'TODATE',  FLAG19 = '*' where  FISCFILE = '0473' and FSC_DATE  = '2017-12-30 00:00:00.000';update  FISCDETL  set  FIELD19 = 'JAN 2018',  FLAG19 = '*' where  FISCFILE = '0473' and FSC_DATE  = '2018-01- 00:00:00.000';update  FISCDETL  set  FIELD19 = 'FEB 2018',  FLAG19 = '*' where  FISCFILE = '0473' and FSC_DATE  = '2018-02- 00:00:00.000';update  FISCDETL  set  FIELD19 = 'MAR 2018',  FLAG19 = '*' where  FISCFILE = '0473' and FSC_DATE  = '2018-03- 00:00:00.000';update  FISCDETL  set  FIELD19 = 'APR 2018',  FLAG19 = '*' where  FISCFILE = '0473' and FSC_DATE  = '2018-04- 00:00:00.000';update  FISCDETL  set  FIELD19 = 'MAY 2018',  FLAG19 = '*' where  FISCFILE = '0473' and FSC_DATE  = '2018-05- 00:00:00.000';update  FISCDETL  set  FIELD19 = 'JUN 2018',  FLAG19 = '*' where  FISCFILE = '0473' and FSC_DATE  = '2018-06- 00:00:00.000'


