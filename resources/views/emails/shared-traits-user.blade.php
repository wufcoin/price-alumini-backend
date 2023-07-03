<h1>Boomer Sooner!</h1>
<h1>{{$user->first_name}} {{$user->last_name}} has the following traits with {{$shared_info['totalCnt'] == 0 ? 0 : round($shared_count * 100 / $shared_info["totalCnt"]) }}%:</h1>

<h2>{{implode(',', $user->job_titles->map(function($i) {return $i->name;})->toArray())}}</h2>

<p>About Me:  {{$user->headline}}</p>

@if ($user->youtube_video_url)
<a href="{{$user->youtube_video_url}}">View {{$user->first_name}}'s video</a>
@endif

<div>
    <h2>Degree:</h2>
    <p>
        @foreach($user->degrees as $v)
            @if(in_array($v->id, $shared_info["degrees"]))
                <span style="color:#982022;">{{$v->name}}</span>
            @elseif
                <span >{{$v->name}}</span>
            @endif
            
            @if(!$loop->last)
                <span>,</span>
            @endif
        @endforeach
    </p>
</div>

<div>
    <h2>Grad level:</h2>
    <p>
        @foreach($user->grad_levels as $v)
            @if(in_array($v->id, $shared_info["grad_levels"]))
                <span style="color:#982022;">{{$v->name}}</span>
            @elseif
                <span >{{$v->name}}</span>
            @endif
            
            @if(!$loop->last)
                <span>,</span>
            @endif
        @endforeach    
    </p>
</div>

<div>
    <h2>JCPenney:</h2>
    <p>{{$user->jc_penny ? 'Yes' : 'No'}}</p>
</div>

<div>
    <h2>In IBC?</h2>
    <p>
        @if(in_array($user->ibc_company->id, $shared_info["ibc_company_id"]))
            <span style="color:#982022;">{{$user->ibc_company->name}}</span>
        @elseif
            <span >{{$user->ibc_company->name}}</span>
        @endif    
    </p>
</div>

<div>
    <h2>Military Affiliation:</h2>
    <p>
        @if(in_array($user->military_branch->id, $shared_info["military_branch"]))
            <span style="color:#982022;">{{$user->military_branch->name}}</span>
        @elseif
            <span >{{$user->military_branch->name}}</span>
        @endif    
    </p>
</div>

<div>
    <h2>Student Orgs:</h2>
    <p>
        @foreach($user->student_orgs as $v)
            @if(in_array($v->id, $shared_info["student_orgs"]))
                <span style="color:#982022;">{{$v->name}}</span>
            @elseif
                <span >{{$v->name}}</span>
            @endif
            
            @if(!$loop->last)
                <span>,</span>
            @endif
        @endforeach     
    </p>
</div>

<div>
    <h2>Professional Associations:</h2>
    <p>
        @foreach($user->associations as $v)
            @if(in_array($v->id, $shared_info["associations"]))
                <span style="color:#982022;">{{$v->name}}</span>
            @elseif
                <span >{{$v->name}}</span>
            @endif
            
            @if(!$loop->last)
                <span>,</span>
            @endif
        @endforeach     
    </p>
</div>

<div>
    <h2>Industries:</h2>
    <p>
        @foreach($user->industries as $v)
            @if(in_array($v->id, $shared_info["industries"]))
                <span style="color:#982022;">{{$v->name}}</span>
            @elseif
                <span >{{$v->name}}</span>
            @endif
            
            @if(!$loop->last)
                <span>,</span>
            @endif
        @endforeach
    </p>
</div>

<div>
    <h2>Hobbies & Interests:</h2>
    <p>
        @foreach($user->hobbies as $v)
            @if(in_array($v->id, $shared_info["hobbies"]))
                <span style="color:#982022;">{{$v->name}}</span>
            @elseif
                <span >{{$v->name}}</span>
            @endif
            
            @if(!$loop->last)
                <span>,</span>
            @endif
        @endforeach
    </p>
</div>

<div>
    <h2>Cities Associate With:</h2>
    <p>
        @foreach($user->cities as $v)
            @if(in_array($v->id, $shared_info["cities"]))
                <span style="color:#982022;">{{$v->name}}</span>
            @elseif
                <span >{{$v->name}}</span>
            @endif
            
            @if(!$loop->last)
                <span>,</span>
            @endif
        @endforeach    
    </p>
</div>

<div>
    <h2>Country or Countries:</h2>
    <p>
        @foreach($user->countries as $v)
            @if(in_array($v->id, $shared_info["countries"]))
                <span style="color:#982022;">{{$v->name}}</span>
            @elseif
                <span >{{$v->name}}</span>
            @endif
            
            @if(!$loop->last)
                <span>,</span>
            @endif
        @endforeach
    </p>
</div>

<div>
    <h2>High School & Other Colleges:</h2>
    <p>
        @foreach($user->schools as $v)
            @if(in_array($v->id, $shared_info["schools"]))
                <span style="color:#982022;">{{$v->name}}</span>
            @elseif
                <span >{{$v->name}}</span>
            @endif
            
            @if(!$loop->last)
                <span>,</span>
            @endif
        @endforeach
    </p>
</div>