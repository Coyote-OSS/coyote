<?php
namespace Neon\View;

enum WorkMode: string
{
    case Stationary = 'stationary';
    case Hybrid = 'hybrid';
    case FullyRemote = 'fullyRemote';
}
