@extends('layouts.app')

@section('content')
<article style="padding-left: 80px; padding-right: 300px; font-size: large">
    <h1>RimWorld Stargate Network</h1>
    <div style="position: absolute; right: 100px">
        <a href="https://github.com/BetterRimworlds/Rimworld-Stargate" target="_blank"><img src="https://avatars.githubusercontent.com/u/69285094?s=200&v=4" /></a>
    </div>
    <p>
        The goal of this project is to facilitate intergalactic trade and the free movement of peoples and goods.
    </p>
    <p>
        See our <a href="https://github.com/BetterRimworlds" target="_blank"><strong>GitHub repo</strong></a>
        for more information!
    </p>
    <p>
        Here is a list of known Stargates. You can register your own below.
    </p>
    <div id="known_gates">
        <table>
        @foreach($gateAddresses as $idx => $gateAddress)
            <tr>
                <td>{{ ++$idx }}.</td>
                <td>{{$gateAddress}}</td>
                <td>{{$gateAddress}}</td>
            </tr>
        @endforeach
        </table>
    </div>
    <div>
        <h3><strong>Publish Your Stargate Address</strong></h3>
        <div id="errors"></div>
        <div><input type="text" id="address" style="font-family: Stargate"/></div>
        <div><button onclick="publishGate()">Publish</button></div>
    </div>
</article>
<script>
function publishGate()
{
    const gateAddress = document.querySelector('input#address').value;

    if (gateAddress.length === 0) {
        return;
    }

    // if (gateAddress.l)

    const url = `/api/known-gates/${gateAddress}`;
    const errorBox = document.querySelector('div#errors');

    errorBox.style.visibility = 'hidden';

    fetch(url, {
        method: 'POST', // Specify the method
        headers: {
            'Content-Type': 'application/json' // Depending on your needs, you might need to set appropriate headers
        },
        // If your POST request includes a body, you'd include it here. For an empty body, you might omit the body property or set it to null
        // body: JSON.stringify({ your: 'payload' })
    }).then(response => {
        if (response.ok) {
            errorBox.style.visibility = 'visible';
            errorBox.innerHTML = 'Stargate successfully registered!';

            const table = document.querySelector('#known_gates table');
            const newRow = document.createElement('tr');
            const newCell1 = document.createElement('td');
            const newCell2 = document.createElement('td');
            const newCell3 = document.createElement('td');

            newCell1.textContent = (table.querySelectorAll('tr').length + 1) + '.';
            newCell2.textContent = gateAddress;
            newCell3.textContent = gateAddress;

            newRow.appendChild(newCell1);
            newRow.appendChild(newCell2);
            newRow.appendChild(newCell3);
            table.appendChild(newRow);

            return;
        }

        return response.text().then(error => { throw new Error(error) });
    }).catch(error => {
        console.error('There has been a problem with your fetch operation:', error);
        errorBox.innerText = error;
        errorBox.style.visibility = 'visible';
    });
}
</script>
@endsection

















